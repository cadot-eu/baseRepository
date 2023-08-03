<?php

namespace App\Repository\base;

use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MessagesRepository
{
    private $errorReceiver, $transport;

    public function __construct(ListableReceiverInterface $errorReceiver, TransportInterface $transport)
    {
        $this->errorReceiver = $errorReceiver;
        $this->transport = $transport;
    }

    public function findAll(): array
    {
        $failedMessages = [];

        // Get all the error messages
        $errorMessages = $this->errorReceiver->all();

        // Process the error messages
        foreach ($errorMessages as $errorMessage) {
            $pourid = $errorMessage->all(TransportMessageIdStamp::class);
            $mainserrors = $errorMessage->all(ErrorDetailsStamp::class)[0]->getFlattenException()->getTraceAsString();
            $exception = $errorMessage->all(ErrorDetailsStamp::class)[0];
            $failedMessages[] = ['class' => $exception->getExceptionClass(), 'code' => $exception->getExceptionCode(), 'message' => $exception->getExceptionMessage(), 'idvideo' => $errorMessage->getMessage()->getVideoId(), 'id' => end($pourid)->getId()];
        }

        return $failedMessages;
    }

    public function retryFailedMessage($id)
    {
        // Get the error message
        $failedEnvelope = $this->errorReceiver->find($id);
        if ($failedEnvelope) {
            $this->errorReceiver->ack($failedEnvelope);
            return true;
        }
        return false;
    }
    public function deleteAllFailedMessages()
    {
        // Get all the error messages
        $errorMessages = $this->errorReceiver->all();

        // Delete each error message
        foreach ($errorMessages as $errorMessage) {
            $pourid = $errorMessage->all(TransportMessageIdStamp::class);
            $id = end($pourid)->getId();
            // Delete the failed message by acknowledging its envelope
            $this->errorReceiver->reject($this->errorReceiver->find($id));
        }
        $count = 0;
        foreach ($this->errorReceiver->all() as $errorMessage) $count++;
        if ($count == 0)
            return true;
        return false;
    }
    public function deleteFailedMessage(int $id): bool
    {
        // Get the error message
        $failedEnvelope = $this->errorReceiver->find($id);
        if ($failedEnvelope) {
            $this->errorReceiver->reject($failedEnvelope);
            return true;
        }
        return false;
    }
    public function showAsyncTasks()
    {
        if ($this->transport instanceof ListableReceiverInterface) {
            $envelopes = $this->transport->all();
        }

        $messages = [];
        foreach ($envelopes as $envelope) {
            $message = $envelope->getMessage();
            $messageClass = get_class($message);
            $pourid = $envelope->all(TransportMessageIdStamp::class);
            $messages[] = ['class' => $messageClass, 'idvideo' => $message->getVideoId(), 'id' => end($pourid)->getId()];
        }
        return $messages;
    }
    public function deleteMessage(int $id): bool
    {
        // Get the error message
        $failedEnvelope = $this->transport->find($id);
        if ($failedEnvelope) {
            $this->transport->reject($failedEnvelope);
            return true;
        }
        return false;
    }
    public function deleteAllMessages()
    {
        foreach ($this->transport->all() as $enveloppe) {
            $this->transport->reject($enveloppe);
        }
        $count = 0;
        foreach ($this->transport->all() as $envelope) $count++;
        if ($count == 0)
            return true;
        return false;
    }
}
