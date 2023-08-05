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
                            <livewire:timer />
                    @else
                            {{ __('app.do_break') }}<br>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const counter = [];
    function loadTimer(czas, lokalizacjaTimera) {
        clearInterval(counter[lokalizacjaTimera]);
        delete counter[lokalizacjaTimera];
        counter[lokalizacjaTimera] = [setInterval(timer, 1000)]; // ustawienie funkcji odpowiedajacej za cykliczne wywolanie(co 1 s) funkcji timer()

        const out = prepareTimer(czas);
        if ($("#"+lokalizacjaTimera).length == 1 ) {
            $("#"+lokalizacjaTimera).html(out); // przypisanie tekstu timera do odpowiedniego elementu html
        }

        function timer() {
            --czas;
            const out = prepareTimer(czas);
            if( $("#"+lokalizacjaTimera).length == 1 ) {
                $("#"+lokalizacjaTimera).html(out); // przypisanie tekstu timera do odpowiedniego elementu html
            }
            if (czas <= 0) {
                $("#"+lokalizacjaTimera).html('');
                //wysyłamy do sprawdzenia czy czas upłynął i czy mamy nowe zadanie
                Livewire.emit('prepareNextTraining');
                return;
            }
        }
    }

    function prepareTimer(secondsTimer) {
        const min = secondsTimer/60; // minuty
        const h = min/60;// godziny
        const d = h/24; // dni
        let sLeft = Math.floor(secondsTimer % 60); // pozostało sekund
        let minLeft = Math.floor(min % 60); // pozostało minut
        let hLeft = Math.floor(h % 24);
        let dLeft = Math.floor(d);
        let dToTmer = '';
        if ( dLeft > 0 ) {
            dToTmer = dLeft+" d.  ";
        }
        // pozostało godzin
        if (minLeft < 10) {
            minLeft = "0" + minLeft;
        }

        if (sLeft < 10) {
            sLeft = "0" + sLeft;
        }

        if (hLeft < 10) {
            hLeft = "0" + hLeft;
        }
        return dToTmer + hLeft + ":" + minLeft + ":" + sLeft; //tekst wyswietlony uzytkownikowi
    }

    window.addEventListener('start_timer', event => {
        loadTimer(event.detail.time, 'timer');
        $('#startTraining').addClass('d-none');
        $('#stopTraining').removeClass('d-none');
    });

    window.addEventListener('start_next_exercise', event => {
        alert(event.detail.title);
    });

    window.addEventListener('stop_timer', event => {
        clearInterval(counter['timer']);
        delete counter['timer'];
    });

</script>
@endsection

