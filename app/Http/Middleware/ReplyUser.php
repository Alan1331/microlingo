<?php
namespace App\Http\Middleware;

use Closure;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ReplyUser
{
    protected $twilioClient;
    protected $twilioWhatsAppNumber;

    public function __construct(Client $twilioClient)
    {
        $this->twilioClient = $twilioClient;
        $this->twilioClient->setLogLevel('debug');
        $this->twilioWhatsAppNumber = config('services.twilio.whatsapp_number');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Code to execute before the request is handled
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param  \Illuminate\Http\Request  $request (important)
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        // Set the maximum execution time to 300 seconds (5 minutes)
        set_time_limit(300);

        // Code to execute after the response has been sent to the browser
        // For example: logging, cleanup tasks, etc.
        $content = $response->getContent();
        $response = json_decode($content, true);

        $recipientNumber = $response['recipient_number'];
        $response = $response['response'];
        $messages = explode('|', $response);
        
        foreach ($messages as $message) {
            if(strlen($message) <= 1600) {
                // send message to user
                $this->sendMessageToUser($recipientNumber, $message);
            } else {
                $splittedMsg = $this->splitMessage($message);
                foreach($splittedMsg as $msg) {
                    // send message to user in chunks
                    $this->sendMessageToUser($recipientNumber, $msg);
                }
            }
        }

        \Log::info('Response has been sent to the browser.');
    }

    private function sendMessageToUser($recipientNumber, $message)
    {
        $this->twilioClient->messages->create(
            $recipientNumber,
            [
                'from' => $this->twilioWhatsAppNumber,
                'body' => $message,
            ]
        );
    }

    private function splitMessage($message, $maxLength = 1600)
    {
        // Explode the message by newline characters
        $lines = explode("\n", $message);
        $messages = [];
        $currentMessage = '';
    
        foreach ($lines as $line) {
            // Check if the current line can fit into the current message
            if (strlen($currentMessage) + strlen($line) + 1 <= $maxLength) {
                // Add line to current message (including the newline character)
                $currentMessage .= $line . "\n";
            } else {
                // If current message is not empty, push it to the array
                if (!empty(trim($currentMessage))) {
                    $messages[] = trim($currentMessage);
                }
                // Start a new message with the current line
                $currentMessage = $line . "\n";
    
                // If the current line is longer than maxLength, split it further by words
                if (strlen($currentMessage) > $maxLength) {
                    $words = explode(' ', $currentMessage);
                    $currentMessage = '';
    
                    foreach ($words as $word) {
                        // Check if the word fits into the current message
                        if (strlen($currentMessage) + strlen($word) + 1 <= $maxLength) {
                            // Add word to current message
                            $currentMessage .= $word . ' ';
                        } else {
                            // Push the current message to the array and start a new message
                            $messages[] = trim($currentMessage);
                            $currentMessage = $word . ' ';
                        }
                    }
    
                    // Push any remaining content of current message to the array
                    if (!empty(trim($currentMessage))) {
                        $messages[] = trim($currentMessage);
                    }
    
                    $currentMessage = '';
                }
            }
        }
    
        // Push the final message
        if (!empty(trim($currentMessage))) {
            $messages[] = trim($currentMessage);
        }
    
        return $messages;
    }
}
