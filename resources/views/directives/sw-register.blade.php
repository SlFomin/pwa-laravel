@if($worker->isAutoRegister() && ($worker->shouldRegisterInDev() || ! app()->environment('local')))
    <script>
        {!! $worker->registrationScript() !!}
    </script>
@endif
