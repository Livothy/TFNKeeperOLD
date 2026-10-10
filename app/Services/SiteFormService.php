<?php

namespace App\Services;

use App\Services\Service;

use DB;
use Illuminate\Support\Arr;

use App\Models\Forms\SiteForm;
use App\Models\Forms\SiteFormOption;
use App\Models\Forms\SiteFormQuestion;
use App\Models\Forms\SiteFormReward;
use App\Models\User\User;

class SiteFormService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | SiteForm Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of forms, as well as posting answers.
    |
    */

    /**
     * Creates a form post.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @param  App\Models\Forms\SiteForm  $form
     */
    public function createSiteForm($data, $user)
    {
        DB::beginTransaction();
        try {
            $data = $this->populateFormData($data);
            $data['user_id'] = $user->id;
            $form = SiteForm::create($data);

            $this->createFormQuestions($data['questions'], $data['options'], $data['is_mandatory'] ?? [], $data['is_multichoice'] ?? [], $form);
            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity']), $form);

            if($form->is_active) $this->alertUsers();

            return $this->commitReturn($form);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a form post.
     *
     * @param  App\Models\Forms\SiteForm  $form
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\SiteForm
     */
    public function updateSiteForm($form, $data, $user)
    {
        DB::beginTransaction();

        try {
            $data = $this->populateFormData($data);
            $data['user_id'] = $user->id;
            $form->update($data);
            $this->updateFormQuestions($data['questions'], $data['options'], $data['is_mandatory'] ?? [], $data['is_multichoice'] ?? [], $form);
            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity']), $form);

            if(isset($data['bump']) && $data['is_active'] == 1 && $data['bump'] == 1) $this->alertUsers();
            
            return $this->commitReturn($form);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a form post and all related answers.
     *
     * @param  App\Models\Forms\SiteForm  $form
     * @return bool
     */
    public function deleteSiteForm($form)
    {
        DB::beginTransaction();

        try {
            if ($form->questions->count() > 0) {
                foreach ($form->questions as $question) {
                    if ($question->options()->count() > 0) $question->options()->delete();
                    if ($question->answers()->count() > 0) {
                        foreach ($question->answers as $answer) {
                            if ($answer->likes()->count() > 0) $answer->likes->each->delete();
                            $answer->delete();
                        }
                    }
                    $question->delete();
                }
            }
            if ($form->rewards()->count() > 0) $form->rewards()->delete();
            $form->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a form.
     *
     * @param  array                  $data
     * @param  App\Models\Forms\SiteForm  $form
     * @return array
     */
    private function populateFormData($data)
    {
        if (isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        $data['is_active'] = isset($data['is_active']);
        $data['is_timed'] = isset($data['is_timed']);
        $data['is_anonymous'] = isset($data['is_anonymous']);
        $data['is_public'] = isset($data['is_public']);
        $data['is_editable'] = isset($data['is_editable']);
        $data['allow_likes'] = isset($data['allow_likes']);
        return $data;
    }

    /**
     * Creates the question and option rows for the form.
     *
     * @param  array                  $questions array of questions by question id [dhfshfs] => "hello how are you?"
     * @param  array                  $options array of options by question id [dhfshfs][1] => "Good!"
     * @param  App\Models\Forms\SiteForm  $form
     * @return array
     */
    private function createFormQuestions($questions, $options, $isMandatory, $isMultichoice, $form)
    {
        $questions = array_filter($questions);
        if (count($questions) <= 0) throw new \Exception("A form must have at least one question.");

        foreach ($questions as $id => $question) {
            if ($id != 'default') { // ignore empty default
                $op = array_filter($options[$id]); //filter to remove null values
                // save question
                $questionEntry = SiteFormQuestion::create([
                    'form_id' => $form->id,
                    'question' => $question,
                    'has_options' => count($op) > 0,
                    'is_mandatory' => isset($isMandatory[$id]) ? 1 : 0,
                    'is_multichoice' => isset($isMultichoice[$id]) ? 1 : 0
                ]);

                //save options
                foreach ($op as $option) {
                    SiteFormOption::create([
                        'question_id' => $questionEntry->id,
                        'option' => $option,
                    ]);
                }
            }
        }
    }

    /**
     * Updates the question and option rows for the form.
     *
     * @param  array                  $questions array of questions by question id [dhfshfs] => "hello how are you?"
     * @param  array                  $options array of options by question id [dhfshfs][1] => "Good!"
     * @param  App\Models\Forms\SiteForm  $form
     * @return array
     */
    private function updateFormQuestions($questions, $options, $isMandatory, $isMultichoice, $form)
    {
        $questions = array_filter($questions);

        if (count($questions) <= 0) throw new \Exception("A form must have at least one question.");

        //update exisiting questions...
        foreach ($form->questions as $question) {
            if (isset($questions[$question->id])) {
                $questionData = $questions[$question->id];
                (isset($options[$question->id]) && count($options[$question->id]) > 0) ? $optionsData = array_filter($options[$question->id]) : $optionsData = []; //filter to remove null values

                // update question
                $question->update([
                    'question' => $questionData,
                    'has_options' => count($optionsData) > 0,
                    'is_mandatory' => isset($isMandatory[$question->id]) ? 1 : 0,
                    'is_multichoice' => isset($isMultichoice[$question->id]) ? 1 : 0
                ]);

                //remove question from array so we dont re-create it later
                unset($questions[$question->id]);

                // update or delete existing option if needed
                foreach ($question->options as $option) {

                    if (isset($optionsData[$option->id])) {
                        //update option
                        $option->update([
                            'option' => $optionsData[$option->id],
                        ]);
                    } else {
                        //if option wasnt passed it was removed so we delete it and all associated answers.
                        if ($question->answers->where('option_id', $option->id)->count() > 0) $question->answers->where('option_id', $option->id)->each->delete();

                        $option->delete();
                    }
                    //remove option from array so we dont re-create it later
                    unset($options[$question->id][$option->id]);
                }

                // add new options
                foreach ($optionsData as $optionId => $option) {
                    if ($question->options->where('id', $optionId)->count() <= 0) {
                        SiteFormOption::create([
                            'question_id' => $question->id,
                            'option' => $option,
                        ]);
                    }
                    unset($options[$question->id][$optionId]);
                }
            } else {
                // if question wasnt passed it was removed so we delete it and the related options/answers.
                if ($question->options()->count() > 0) $question->options->each->delete();
                if ($question->answers()->count() > 0) {
                    foreach ($question->answers as $answer) {
                        if ($answer->likes()->count() > 0) $answer->likes->each->delete();
                        $answer->delete();
                    }
                }
                $question->delete();
            }
        }
        //then just create the rest anew if needed
        if (count($questions) > 0) $this->createFormQuestions($questions, $options, $isMandatory, $isMultichoice, $form);
    }

    /**
     * Processes user input for creating/updating form rewards.
     *
     * @param  array                      $data
     * @param  \App\Models\Forms\SiteForm  $form
     */
    private function populateRewards($data, $form)
    {
        // Clear the old rewards...
        $form->rewards()->delete();

        if (isset($data['rewardable_type'])) {
            foreach ($data['rewardable_type'] as $key => $type) {
                SiteFormReward::create([
                    'form_id'       => $form->id,
                    'rewardable_type' => $type,
                    'rewardable_id'   => $data['rewardable_id'][$key],
                    'quantity'        => $data['quantity'][$key],
                ]);
            }
        }
    }

    /**
     * Updates the unread news flag for all users so that
     * the new news notification is displayed.
     *
     * @return bool
     */
    private function alertUsers()
    {
        User::query()->update(['is_polls_unread' => 1]);
        return true;
    }
}
