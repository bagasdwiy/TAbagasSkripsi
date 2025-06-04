<?php
class ErrorHandler {
    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $error_message = "Error [$errno] $errstr\n";
        $error_message .= "Line: $errline\n";
        $error_message .= "File: $errfile\n";
        
        // Log error
        error_log($error_message, 3, __DIR__ . '/../logs/error.log');
        
        if (DEBUG_MODE) {
            echo "<div class='error-message'>";
            echo "<h2>Error Occurred</h2>";
            echo "<pre>$error_message</pre>";
            echo "</div>";
        } else {
            echo "<div class='error-message'>An error occurred. Please try again later.</div>";
        }

        return true;
    }

    public static function handleException($exception) {
        $error_message = "Exception: " . $exception->getMessage() . "\n";
        $error_message .= "Line: " . $exception->getLine() . "\n";
        $error_message .= "File: " . $exception->getFile() . "\n";
        $error_message .= "Trace:\n" . $exception->getTraceAsString();
        
        // Log error
        error_log($error_message, 3, __DIR__ . '/../logs/error.log');
        
        if (DEBUG_MODE) {
            echo "<div class='error-message'>";
            echo "<h2>Exception Occurred</h2>";
            echo "<pre>$error_message</pre>";
            echo "</div>";
        } else {
            echo "<div class='error-message'>An error occurred. Please try again later.</div>";
        }
    }
}

// Set error handlers
set_error_handler([ErrorHandler::class, 'handleError']);
set_exception_handler([ErrorHandler::class, 'handleException']);
?> 