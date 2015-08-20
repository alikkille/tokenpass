<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>Verify Your Email Address</h2>

        <p>
            Thanks for creating a Tokenly Account.
        </p>

        <p></p>
            Please follow this link to verify your email address:<br/>
            {{ URL::route('auth.verify', ['token' => $token]) }}.
        </p>

        <p>
            This link will expire in 12 hours.
        </p>

        <p>
            Thanks.
        </p>

        <p>
            - The Tokenly Team
        </p>


    </body>
</html>
