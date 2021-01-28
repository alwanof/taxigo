<p class="mt-8 text-center text-xs text-80">
    <a href="https://marasielapp.com" class="text-primary dim no-underline">Marasiel Taxi</a>
    <span class="px-1">&middot;</span>
    &copy; {{ date('Y') }} Marasiel Taxi< - By Totil IT Group. <span class="px-1">&middot;</span>
        v0.0.1
        <div class="text-center mt-4">
            @if (auth()->user()->level == 2)
                <a href="{{ env('APP_URL') }}/taxi/{{ auth()->user()->email }}" target="_blank">


                    {!! QrCode::size(250)->generate(env('APP_URL') . '/taxi/' . auth()->user()->email) !!}
                </a>
            @endif


        </div>

        @if ($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] == 'marasiel.sy/control/dashboards/main')
            <script>
                window.location = "https://www.marasiel.com/dashboards/main";

            </script>
        @endif


</p>
