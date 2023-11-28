<?php
namespace App\Custom;

use Illuminate\Support\Facades\Log;
use App\Models\Message;

class Conversation {
    private $messages = [];
    public $userMessageCount = 0;
    public $assistantMessageCount = 0;
    public $maxUserMessages = 30; // The maximum number of messages a user can send before the conversation is ended

    public function __construct($messages = []) {
        foreach($messages as $message) {
            $this->addMessage($message);
        }
    }

    public function addMessage(Message $message) {
        $this->messages[] = $message;

        if($message->role === 'user') {
            $this->userMessageCount++;
        } else {
            $this->assistantMessageCount++;
        }
    }

    /**
     * Function to return all messages
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * Function to return all messages
     */
    public function clearMessages() {
        return $this->messages = [];
    }

    /**
     * A function which returns all messages as an array of messages
     */
    public function getAllMessages()
    {
        return $this->messages;
    }

    /**
     * A function which returns all messages as an array
     */
    public function getMessagesAsArray(): Array
    {
        $messagesAsArray = array_map(function($object) {
            return (array) $object;
        }, $this->messages);

        return $messagesAsArray;
    }

    /**
     * A function which returns the first message
     */
    public function getFirstMessage() {
        if(count($this->messages) === 0)
        {
            return null;
        }
        else
        {
            return $this->messages[0];
        }
    }

    /**
     * A function which returns the last message
     */
    public function getLastMessage() {
        if(count($this->messages) === 0)
        {
            return null;
        }
        elseif (count($this->messages) === 1)
        {
            return $this->messages[0];
        }
        else
        {
            return $this->messages[count($this->messages) - 1];
        }
    }

    public function getConversationLength() {
        return count($this->messages);
    }

    public function print()
    {
        if(count($this->messages) === 0)
        {
            echo "No messages in conversation\n";
            return;
        }

        // echo each message as role: content
        foreach($this->messages as $message) {
            echo $message->role . ': ' . print_r($message->content, true) . "\n";
        }
    }

    public function userMessageCountExceeded () {
        return $this->userMessageCount >= $this->maxUserMessages;
    }
}
