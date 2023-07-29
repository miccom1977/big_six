@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('app.dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (count($exercises) > 0)
                        @foreach($exercises as $singleExercise)
                            @if ($singleExercise['step1Done'] == 0)
                                Wykonaj zadanie {{ __('exercises.' . $singleExercise['name']) }} Poziom Początkujący<br>
                            @elseif ($singleExercise['step2Done'] == 0)
                                Wykonaj zadanie {{ __('exercises.' . $singleExercise['name']) }} Poziom Treningowy<br>
                            @elseif ($singleExercise['step3Done'] == 0)
                                Wykonaj zadanie {{ __('exercises.' . $singleExercise['name']) }} Poziom Przejścia<br>
                            @endif
                                Wykonaj {{ $singleExercise['seriesToDo'] }} serii/ie po {{ $singleExercise['repetitions'] }} powtórzeń<br>
                        @endforeach
                    @else
                        Dzisiaj nie trenujemy!<br>
                        Twoje mięśnie, ścięgna i kości potrzebują regeneracji :) <br>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
