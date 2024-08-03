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
        $this->twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');
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
     * @param  \Illuminate\Http\Request  $request
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

        $recipient_number = $response['recipient_number'];
        $response = $response['response'];
        $messages = explode('|', $response);
        
        foreach ($messages as $message) {
            Log::info("Sending message: " . $message);
            if(str_contains($message, '/storage/videos/')) {
                $videos = [$message];
                // send video if contains typical video url
                $this->sendMessageToUser($recipient_number, 'Sending video', $videos);
            } else {
                // send message instead
                $this->sendMessageToUser($recipient_number, $message);
            }
        }

        \Log::info('Response has been sent to the browser.');
    }

    private function sendMessageToUser($recipient_number, $message, $mediaUrls=null)
    {
        if($mediaUrls != null) {
            Log::info("mediaUrls type: ". gettype($mediaUrls));
            
            $this->twilioClient->messages->create(
                $recipient_number,
                [
                    'mediaUrl' => $mediaUrls,
                    'from' => $this->twilioWhatsAppNumber,
                    'body' => $message,
                ]
            );
        } else {
            $this->twilioClient->messages->create(
                $recipient_number,
                [
                    'from' => $this->twilioWhatsAppNumber,
                    'body' => $message,
                ]
            );
        }
    }
}
