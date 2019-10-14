<?php

namespace App\Http\Controllers\Traits;

use App\User;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

trait Notifications
{
    public function notifyUser($parent, $data)
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder('New event for '.$parent->name);
        $notificationBuilder->setBody($data["text"])
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($data);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $users = User::all()->where("parent_id", $parent->id);
        $tokens = array();
        foreach ($users as $user) {
            if (!empty($user->device_id))
                $tokens[] = $user->device_id;
        }
        if (sizeof($tokens) == 0)
            return response()->json("The user doesn't have any registered device", Response::HTTP_PRECONDITION_FAILED); //412

        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

        return [
            'name' => $parent->name,
            'user' => $tokens,
            'numberSuccess' => $downstreamResponse->numberSuccess(),
            'numberFailure' => $downstreamResponse->numberFailure(),
            'numberModification' => $downstreamResponse->numberModification()
        ];
    }
}
