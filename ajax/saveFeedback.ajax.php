<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Feedback\FeedbackArrangor;
use UKMNorge\Feedback\FeedbackResponse;


$handleCall = new HandleAPICall(['question', 'answerId'], ['answerText'], ['GET', 'POST'], false);

$campaignId = 2; // Campaign ID for the feedback campaign i DB
$question = $handleCall->getArgument('question');
$answerId = $handleCall->getArgument('answerId');
$answerText = $handleCall->getOptionalArgument('answerText');

$userId = get_current_user_id();

$answer = !empty($answerText) ? $answerText : $answerId;

$feedback = new FeedbackArrangor(-1, [], $userId, $campaignId);

$feedback->leggTilResponse(new FeedbackResponse(-1, $question, $answer));

$res = $feedback->save();

$handleCall->sendToClient([
    'result' => $res,
]);