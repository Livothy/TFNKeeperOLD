<?php

namespace App\Http\Controllers;

use App\Models\Character\CharacterDesignUpdate;
use App\Models\Prompt\PromptCategory;
use App\Models\Submission\Submission;
use Illuminate\Http\Request;

class QueueController extends Controller {
    /**
     * Shows the queue index page.
     *
     * @param string $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueueIndex(Request $request, $status = null) {
        $submissions = Submission::with('prompt')->where('status', $status ? ucfirst($status) : 'Pending')->whereNotNull('prompt_id');
        $designs = CharacterDesignUpdate::where('status', $status ? ucfirst($status) : 'Pending');
        $data = $request->only(['prompt_category_id', 'sort']);
        if (isset($data['prompt_category_id']) && $data['prompt_category_id'] != 'none') {
            $submissions->whereHas('prompt', function ($query) use ($data) {
                $query->where('prompt_category_id', $data['prompt_category_id'])->whereNot('public_queue', '=', 0);
            });
        } else {
            $submissions->whereHas('prompt', function ($query) {
                $query->whereNot('public_queue', '=', 0);
            });
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $submissions->sortNewest();
                    break;
                case 'oldest':
                    $submissions->sortOldest();
                    break;
            }
        } else {
            $submissions->sortOldest();
        }

        return view('queues.queues', [
            'submissions' => $submissions->paginate(30)->appends($request->query()),
            'designs'     => $designs->paginate(30)->appends($request->query()),
            'categories'  => ['none' => 'Any Category'] + PromptCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'isClaims'    => false,
        ]);
    }
}
