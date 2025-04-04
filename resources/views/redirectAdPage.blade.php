<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x:head></x:head>
    </head>
    <body class="antialiased">
    
    <div class="container-fluid">

        <div class="row">

            <div class="col">
                <h1>Redirecting in <span id="countdown">5</span> seconds...</h1>
                <p>Please wait while we take you to your destination.</p>
            </div>

        </div>


        <script>
            let countdown = 5; // Countdown in seconds
            const redirectURL = "https://example.com"; // Target URL

            function startCountdown() {
                const countdownElement = document.getElementById("countdown");
                const interval = setInterval(() => {
                    countdownElement.innerText = countdown;
                    if (countdown <= 0) {
                        clearInterval(interval);
                        window.location.href = redirectURL; // Redirect when countdown ends
                    }
                    countdown--;
                }, 1000);
            }

            window.onload = startCountdown;
        </script>
    </body>


    
</html>
