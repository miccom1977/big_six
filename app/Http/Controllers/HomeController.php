<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Services\ExerciseService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(public ExerciseService $exerciseService)
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        $exStages = explode('_', Auth::user()->stage);

        /*  Wielka Szóstka:
         * 1. Pompki
         * 2. Przysiady
         * 3. Podciągnięcia
         * 4. Brzuch
         * 5. Mostek
         * 6. Barki
         */
        $i = 1;
        $dayOfTheWeek = Carbon::now()->dayOfWeek;

        $iAllTrainings = Exercise::where('user_id', Auth::user()->id)->count();
        // sprawdzamy, na jakim etapie jest zawodnik
        $workStep = $this->exerciseService->getWorkStep($exStages, $iAllTrainings);
        list($workDay, $exerciseToDo) = $this->exerciseService->getExercisesList($workStep, $dayOfTheWeek, 0);
        if($workStep == 2) {
            list($workDay, $exerciseToDo) = $this->exerciseService->getExercisesList($workStep, $dayOfTheWeek, 1);
        }
        $exercises = [];
        if (in_array($dayOfTheWeek, $workDay)) {
            // dzień treningu
            $exercises = $this->exerciseService->getExercises($exerciseToDo);
        }
        return view('home', compact('exercises'));
    }
}
