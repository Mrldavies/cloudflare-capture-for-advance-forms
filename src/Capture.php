<?php

namespace mrldavies\CCAF;

class Capture
{
    protected $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    protected $siteKey;
    protected $secretKey;

    public function __construct($siteKey, $secretKey)
    {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
    }

    public function init()
    {
        if ($this->siteKey && $this->secretKey) {
            add_action('wp_enqueue_scripts', [$this, 'addTurnstileScript'], 100);
            add_action('af/form/after_fields', [$this, 'addTurnstileField'], 10, 2);
            add_action('af/form/before_submission', [$this, 'validateTurnstileResponse'], 10, 3);
        }
    }

    public function addTurnstileScript()
    {
        wp_enqueue_script(
            'turnstile',
            'https://challenges.cloudflare.com/turnstile/v0/api.js',
            [],
            null,
            true
        );
    }

    public function addTurnstileField($form, $args)
    {
        echo '<div class="cf-turnstile" data-sitekey="' . $this->siteKey . '"></div>';
    }

    public function validateTurnstileResponse($form, $fields, $args)
    {
        $request = $this->sendVerificationRequest();

        if (is_wp_error($request)) {
            $this->handleError('There was a problem submitting the form.', $request->get_error_message());
        }

        $response = wp_remote_retrieve_body($request);
        $decodedResponse = json_decode($response, true);

        if (!$decodedResponse['success']) {
            $this->handleError('The capture failed please try again.', implode(', ', $decodedResponse['error-codes']), 401);
        }
    }

    private function sendVerificationRequest()
    {
        return wp_remote_post($this->url, [
            'body' => [
                'secret' => $this->secretKey,
                'response' => $_POST['cf-turnstile-response'],
                'remoteip' => $_SERVER['REMOTE_ADDR'],
            ]
        ]);
    }

    private function handleError($message, $error, $code = 500)
    {
        wp_send_json_error([
            'errors' => [
                ['message' => $message],
            ],
        ], $code);

        error_log($error);
        wp_die();
    }
}
