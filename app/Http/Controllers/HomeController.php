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
        // sprawdzamy, na jakim etapie jest zawodnik
        $workStep = $this->exerciseService->getWorkStep($exStages);
        // pobieramy listę zadań do wykonania
        $exerciseToDo = $this->exerciseService->getExercisesList($workStep, $dayOfTheWeek, 1);
        // sprawdzamy, czy użytkownik ma jeszcze do wykonania jakieś zadania
        $exercises = $this->exerciseService->getExercises($exerciseToDo);
        return view('home', compact('exercises'));
    }
}
