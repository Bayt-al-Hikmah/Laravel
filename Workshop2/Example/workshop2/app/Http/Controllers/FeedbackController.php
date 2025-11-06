<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\StoreFeedbackRequest; 


class FeedbackController extends Controller
{

   public function showForm()
    {
        return view('feedback.form');
    }

    
    public function storeFeedback(StoreFeedbackRequest $request)
    {
    
        $validatedData = $request->validated();
        
        $feedbacks = $request->session()->get('feedbacks', []);
        $feedbacks[] = $validatedData;
        $request->session()->put('feedbacks', $feedbacks);
            
        return redirect()->route('feedbacks');
    }


    public function feedbackList(Request $request){
        $feedbacks = $request->session()->get('feedbacks', []);
        return view('feedback.feedbacks', ['feedback_list' => $feedbacks]);
    }
}