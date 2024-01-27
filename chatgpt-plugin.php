<?php
/**
 * Plugin Name: ChatGPT Integration
 * Description: A ChatGPT integration that adds a floating chat window to your site.
 * Version: 1.0
 * Author: WinterR
 */

function chatgpt_enqueue_scripts() {
    wp_enqueue_style('chatgpt-style', plugins_url('/css/style.css', __FILE__));
    wp_enqueue_script('chatgpt-script', plugins_url('/js/script.js', __FILE__), array('jquery'), '1.0', true);
}

add_action('wp_enqueue_scripts', 'chatgpt_enqueue_scripts');


add_action('rest_api_init', function () {
    register_rest_route('chatgpt/v1', '/message/', array(
        'methods' => 'POST',
        'callback' => 'handle_chatgpt_request',
    ));
});

function handle_chatgpt_request(WP_REST_Request $request) {
    $parameters = $request->get_json_params();
    $message = $parameters['message'] ?? '';

    if (!empty($message)) {
        $response = send_message_to_openai($message);
        return new WP_REST_Response($response, 200);
    } else {
        return new WP_Error('empty_message', 'No message provided', array('status' => 422));
    }
}

function send_message_to_openai($message) {
    $api_key = OPENAI_API_KEY;
    $ch = curl_init('https://api.openai.com/v1/chat/completions'); // Update the URL to the desired model's endpoint

    $data = array(
        'model' => 'gpt-3.5-turbo',
        'messages' => array(
            array(
                'role' => 'user',
                'content' => $message,
            ),
        ),
        'temperature' => 0.7,
    );

    $temp = json_encode($data);

    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    );

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
