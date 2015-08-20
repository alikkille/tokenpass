<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>Reset Your Password</h2>

        <p>
            Someone requested to reset the Tokenly Accounts password with this email address.  If this was not you, just ignore this message.
        </p>

        <p>
            Click here to reset your password: {{ url('password/reset/'.$token) }}
        </p>

        <p>
            This link will expire in 1 hour.
        </p>

        <p>
            Thanks.
        </p>

        <p>
            - The Tokenly Team
        </p>

    </body>
</html>
