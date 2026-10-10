<?php

namespace App\Services;

use App\Services\Service;

use DB;

use App\Models\Forms\SiteFormAnswer;
use App\Models\Forms\SiteFormLike;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;

class SiteFormManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | SiteForm Manager
    |--------------------------------------------------------------------------
    |
    | Handles the editing of forms, as well as posting answers from user side.
    |
    */

    public function postSiteForm($form, $data, $user) {
        DB::beginTransaction();
        try {
            $isEdit = isset($data['action']) && $data['action'] == 'edit';
            $submissionNumber = $data['submission_number'] ?? 0;
            // TFN: every new response must pass canSubmit (it used to be skipped unless action=submit was sent),
            // the form must be open, and edits must target the user's own existing response.
            if (!\App\Models\Forms\SiteForm::visible()->where('id', $form->id)->exists()) throw new \Exception("This form is not open.");
            if ($isEdit) {
                if (!$form->is_editable) throw new \Exception("This form cannot be edited.");
                if ($form->is_timed && $form->end_at < \Carbon\Carbon::now()) throw new \Exception("This form is closed.");
                if (!SiteFormAnswer::where('form_id', $form->id)->where('user_id', $user->id)->where('submission_number', $submissionNumber)->exists()) throw new \Exception("Invalid response selected.");
            } elseif ($form->canSubmit($user) !== true) {
                throw new \Exception("This form cannot be submitted at the time.");
            }
            // chosen options must belong to their question
            foreach ($form->questions as $question) {
                if ($question->has_options && isset($data[$question->id])) {
                    $chosen = array_filter((array) $data[$question->id]);
                    if (count($chosen) && $question->options()->whereIn('id', $chosen)->count() != count(array_unique($chosen))) throw new \Exception("Invalid option selected.");
                    if (!$question->is_multichoice && count($chosen) > 1) throw new \Exception("Invalid option selected.");
                }
            }

            $nextNumber = $form->latestSubmissionNumber() + 1;
            foreach ($form->questions as $key => $question) {
                $existingAnswer = SiteFormAnswer::where('user_id', $user->id)->where('question_id', $question->id)->where('submission_number', $submissionNumber)->first();
                // Is edit and not a valid multichoice question
                if ($isEdit && $existingAnswer && !($question->is_multichoice && $question->has_options)) {
                    //update existing answer
                    $answer = $data[$question->id];
                    if ($question->is_mandatory && empty($answer)) throw new \Exception("Question " . $key + 1 . " cannot be empty, as it is mandatory!");
                    if (array_key_exists($question->id, $data)) {
                        if ($question->has_options) {
                            $existingAnswer->update([
                                'option_id' => $answer,
                            ]);
                        } else {
                            $existingAnswer->update([
                                'answer' => $answer,
                            ]);
                        }
                    }
                    // Cause if it didn't have options multichoice doesn't mean anything
                } else if ($question->is_multichoice && $question->has_options) {
                    // Always clear out existing answers for multi-choice easier than trying to figure out which to remove / update / etc.
                    $answers = SiteFormAnswer::where('user_id', $user->id)->where('question_id', $question->id)->where('submission_number', $submissionNumber);
                    $answers->delete();
                    if (isset($data[$question->id])) {
                        $answers = $data[$question->id];
                        foreach ($answers as $key => $answer) {
                            SiteFormAnswer::create([
                                'form_id' => $form->id,
                                'question_id' => $question->id,
                                'option_id' => $answer,
                                'user_id' => $user->id,
                                'submission_number' => $isEdit ? $submissionNumber : $nextNumber
                            ]);
                        }
                    }
                } else {
                    //save new answer
                    if (isset($data[$question->id])) {
                        $answer = $data[$question->id];
                        if ($question->has_options) {
                            SiteFormAnswer::create([
                                'form_id' => $form->id,
                                'question_id' => $question->id,
                                'option_id' => $answer,
                                'user_id' => $user->id,
                                'submission_number' => $isEdit ? $submissionNumber : $nextNumber
                            ]);
                        } else {
                            SiteFormAnswer::create([
                                'form_id' => $form->id,
                                'question_id' => $question->id,
                                'answer' => $answer,
                                'user_id' => $user->id,
                                'submission_number' => $isEdit ? $submissionNumber : $nextNumber
                            ]);
                        }
                    } else {
                        if ($question->is_mandatory) throw new \Exception("Question " . $key + 1 . " cannot be empty, as it is mandatory!");
                        //allow empty answers
                        SiteFormAnswer::create([
                            'form_id' => $form->id,
                            'question_id' => $question->id,
                            'user_id' => $user->id,
                            'submission_number' => $isEdit ? $submissionNumber : $nextNumber
                        ]);
                    }
                }
            }

            if ($isEdit) {
                // no rewards for edits...
                $rewardsString = '';
            } else {
                // only reward initial post
                $rewardsString = $this->rewardUser($form, $user);
            }

            $this->commitReturn(true);
            return $rewardsString;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Likes an answer.
     */
    public function postLikeAnswer($answer, $user) {
        DB::beginTransaction();
        try {
            if (!$answer->form || !$answer->form->allow_likes || !$answer->form->is_public) throw new \Exception("Answers on this form cannot be liked.");
            $like = SiteFormLike::where('user_id', $user->id)->where('answer_id', $answer->id)->first();
            if ($like) throw new \Exception("You have already liked this answer!");

            SiteFormLike::create([
                'user_id' => $user->id,
                'answer_id' => $answer->id,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Un-Likes an answer.
     */
    public function postUnlikeAnswer($answer, $user) {
        DB::beginTransaction();
        try {
            $like = SiteFormLike::where('user_id', $user->id)->where('answer_id', $answer->id)->first();
            if (!$like) throw new \Exception("You cannot remove a like that does not exist!");
            $like->delete();
            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes reward data into a format that can be used for distribution.
     *
     * @param  array $data
     * @return array
     */
    private function processRewards($data) {

        $assets = createAssetsArray(false);
        // Process the additional rewards
        if (isset($data['rewardable_type']) && $data['rewardable_type']) {
            foreach ($data['rewardable_type'] as $key => $type) {
                $reward = null;
                switch ($type) {
                    case 'Item':
                        $reward = Item::find($data['rewardable_id'][$key]);
                        break;
                    case 'Currency':
                        $reward = Currency::find($data['rewardable_id'][$key]);
                        if (!$reward->is_user_owned) throw new \Exception("Invalid currency selected.");
                        break;
                    case 'LootTable':
                        $reward = LootTable::find($data['rewardable_id'][$key]);
                        break;
                    case 'Raffle':
                        $reward = Raffle::find($data['rewardable_id'][$key]);
                        break;
                }
                if (!$reward) continue;
                addAsset($assets, $reward, $data['quantity'][$key]);
            }
        }
        return $assets;
    }

    private function rewardUser($form, $user) {
        $assets = [];
        // distribute rewards if applicable
        if ($form->rewards->count() > 0) {
            // Get the updated set of rewards
            $rewardData = [];
            $rewardData['rewardable_type'] = [];
            $rewardData['rewardable_id'] = [];
            $rewardData['quantity'] = [];

            foreach ($form->rewards as $reward) {
                $rewardData['rewardable_type'][] = $reward->rewardable_type;
                $rewardData['rewardable_id'][] = $reward->rewardable_id;
                $rewardData['quantity'][] = $reward->quantity;
            }
            $rewards = $this->processRewards($rewardData);
            // Distribute user rewards
            $assets = fillUserAssets($rewards, null, $user, 'Form Rewards', [
                'data' => 'Received rewards from form (<a href="' . $form->url . '">' . $form->title . '</a>)'
            ]);
            if (!$assets) throw new \Exception("Failed to distribute rewards to user.");
        }
        $rewardsString = count($assets) > 0 ? 'As a reward, you have received: ' . createRewardsString($assets) : '';
        if (!isset($rewardsString)) throw new \Exception("Reward string could not be built.");
        return $rewardsString;
    }
}
