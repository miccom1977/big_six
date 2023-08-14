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
                            <div id="exerciseElement" work_id="{{ $singleExercise['work_id'] }}" step="{{ $singleExercise['step'] }}" class="border border-dark rounded-lg shadow p-4 mb-4 bg-white rounded">
                                Wykonaj zadanie {{ __('exercises.' . $singleExercise['name']) }} {{ __('app.step' . $singleExercise['step']) }}<br>
                                @for ($i = 1; $i <= $singleExercise['seriesEnd']; $i++)
                                    Wykonaj 1 serię po {{ $singleExercise['repetitions'] }} powtórzeń
                                @if ($singleExercise['seriesDoIt'] > 0)
                                        @php $singleExercise['seriesDoIt']-- @endphp
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                        </svg>
                                @endif
                                    <br>
                                @endfor
                            </div>
                        @endforeach
                            <livewire:timer />
                    @else
                            {!! __('app.do_break') !!}<br>
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
        counter[lokalizacjaTimera] = [setInterval(timer, 1000)];
        // ustawienie funkcji odpowiadającej za cykliczne wywołanie (co 1 s) funkcji timer()

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
                const params = {
                    work_id: $('#exerciseElement').attr('work_id'),
                    step: $('#exerciseElement').attr('step'),
                    ex_id: $('#exerciseElement').attr('ex_id'),
                }
                //wysyłamy do sprawdzenia czy czas upłynął i czy mamy nowe zadanie
                Livewire.emit('prepareNextTraining', params);
                clearInterval(counter[lokalizacjaTimera]);
                delete counter[lokalizacjaTimera];
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

