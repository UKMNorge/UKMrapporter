<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Feedback\Feedbacks;

$campaignId = 2; // Campaign ID for the feedback campaign i DB

$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);
$userId = get_current_user_id();

$feedbacks = Feedbacks::getAllFeedbacksUserCampaign($userId, $campaignId);

$handleCall->sendToClient([
    'hasAnswered' => count($feedbacks) > 0,
]);
