<?php namespace Naraki\Mail\Support;

use Mailgun\Message\MessageBuilder;

class Message
{
    /**
     * @var \Mailgun\Message\MessageBuilder
     */
    protected $builder;
    /**
     * @var array
     */
    protected $variables = [];
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $config;

    /**
     * Message constructor.
     *
     * @param \Mailgun\Message\MessageBuilder $messageBuilder
     * @param \Illuminate\Support\Collection $config
     */
    public function __construct(MessageBuilder $messageBuilder, $config)
    {
        $this->builder = $messageBuilder;
        $this->config = $config;
        $this->setConfigTestMode();
    }

    /**
     * Set from address
     *
     * @param string $address
     * @param string $name
     *
     * @return $this
     */
    public function from($address, $name = ''): self
    {
        $this->builder->setFromAddress($address, ['full_name' => $name]);
        return $this;
    }

    /**
     * Add a recipient to the message.
     *
     * @param string|array $address
     * @param string $name
     * @param array $variables
     *
     * @return \Naraki\Mail\Support\Message
     * @throws \Mailgun\Message\Exceptions\TooManyRecipients
     */
    public function to($address, $name = '', array $variables = []): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $variables) {
                $this->variables[$email] = $variables;
                $name = $variables['name'] ?: null;
                $this->builder->addToRecipient($email, ['full_name' => $name]);
            }
        } else {
            if (!empty($variables)) {
                $this->variables[$address] = $variables;
            }
            $this->builder->addToRecipient($address, ['full_name' => $name]);
        }
        return $this;
    }

    /**
     * Add a carbon copy to the message.
     *
     * @param string|array $address
     * @param string $name
     * @param array $variables
     *
     * @return \Naraki\Mail\Support\Message
     * @throws \Mailgun\Message\Exceptions\TooManyRecipients
     */
    public function cc($address, $name = '', array $variables = []): self
    {
        if (!empty($variables)) {
            $this->variables[$address] = $variables;
        }
        $this->builder->addCcRecipient($address, ['full_name' => $name]);
        return $this;
    }

    /**
     * Add a blind carbon copy to the message.
     *
     * @param string|array $address
     * @param string $name
     * @param array $variables
     *
     * @return \Naraki\Mail\Support\Message
     * @throws \Mailgun\Message\Exceptions\TooManyRecipients
     */
    public function bcc($address, $name = '', array $variables = []): self
    {
        if (!empty($variables)) {
            $this->variables[$address] = $variables;
        }
        $this->builder->addBccRecipient($address, ['full_name' => $name]);
        return $this;
    }

    /**
     * Set/Overwrite recipientVariables
     *
     * @param array $variables
     * @return void
     */
    public function recipientVariables(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * Add a reply-to address to the message.
     *
     * @param string $address
     * @param string $name
     *
     * @return \Naraki\Mail\Support\Message
     */
    public function replyTo($address, $name = ''): self
    {
        $this->builder->setReplyToAddress($address, ['full_name' => $name]);
        return $this;
    }

    /**
     * Set the subject of the message.
     *
     * @param string $subject
     *
     * @return \Naraki\Mail\Support\Message
     */
    public function subject($subject): self
    {
        $this->builder->setSubject($subject);
        return $this;
    }

    /**
     * Attach a file to the message.
     *
     * @param string $path
     * @param string $name
     *
     * @return \Naraki\Mail\Support\Message
     */
    public function attach($path, $name = ''): self
    {
        $this->builder->addAttachment($path, $name);
        return $this;
    }

    /**
     * Embed a file in the message and get the CID.
     *
     * @param string $path
     * @param string $name
     *
     * @return string
     */
    public function embed($path, $name = null): string
    {
        $name = $name ?: basename($path);
        $this->builder->addInlineImage(sprintf('@%s', $path), $name);
        return sprintf('cid:%s', $name);
    }

    /**
     * Add Mailgun tags to the message.
     * Tag limit is 3.
     *
     * @param string|array $tags
     *
     * @return \Naraki\Mail\Support\Message
     * @throws \Mailgun\Message\Exceptions\LimitExceeded
     */
    public function tag($tags): self
    {
        foreach ($tags as $tag) {
            $this->builder->addTag($tag);
        }
        return $this;
    }

    /**
     * Add Mailgun campaign ID(s) to the message
     * Campaign ID limit is 3.
     *
     * @param int|string|array $campaigns
     *
     * @return \Naraki\Mail\Support\Message
     * @throws \Mailgun\Message\Exceptions\LimitExceeded
     */
    public function campaign($campaigns): self
    {
        foreach ($campaigns as $campaign) {
            $this->builder->addCampaignId($campaign);
        }
        return $this;
    }

    /**
     * Enable/disable DKIM signature on per-message basis.
     *
     * @param bool $enabled
     *
     * @return \Naraki\Mail\Support\Message
     */
    public function dkim($enabled): self
    {
        $this->builder->setDkim($enabled);
        return $this;
    }

    /**
     * Toggles clicks tracking on a per-message basis.
     * This setting has a higher priority than the domain-level setting.
     *
     * @param bool $value
     *
     * @return \Naraki\Mail\Support\Message
     */
    public function trackClicks($value): self
    {
        $this->builder->setClickTracking($value);
        return $this;
    }

    /**
     * Toggles opens-tracking on a per-message basis.
     * This setting has a higher priority than the domain-level setting.
     *
     * @param bool $enabled
     *
     * @return \Naraki\Mail\Support\Message
     */
    public function trackOpens($enabled): self
    {
        $this->builder->setOpenTracking($enabled);
        return $this;
    }

    /**
     * Enable or disable test-mode on a per-message basis.
     *
     * @param bool $enabled
     *
     * @return \Naraki\Mail\Support\Message
     */
    public function testmode($enabled): self
    {
        $this->builder->setTestMode($enabled);
        return $this;
    }

    /**
     * Append a custom MIME header to a message.
     *
     * @param string $key
     * @param string $value
     *
     * @return \Naraki\Mail\Support\Message
     */
    public function header($key, $value): self
    {
        $this->builder->addCustomHeader($key, $value);
        return $this;
    }

    /**
     * Attach custom data to a message.
     *
     * @param string $key
     * @param mixed $data
     *
     * @return \Naraki\Mail\Support\Message
     */
    public function data($key, $data): self
    {
        $this->builder->addCustomData($key, $data);
        return $this;
    }

    /**
     * Apply reply-to address from config.
     */
    protected function setConfigReplyTo()
    {
        $address = $this->config->get('reply_to.address');
        $name = $this->config->get('reply_to.name');
        if ($address) {
            $name = $name ? ['full_name' => $name] : null;
            $this->builder->setReplyToAddress($address, $name);
        }
    }

    /**
     * Enable/Disable test-mode depending on config setting.
     */
    protected function setConfigTestMode()
    {
        $this->builder->setTestMode($this->config->get('test_mode'));
    }

    /**
     * Set from address from config
     */
    protected function setConfigFrom()
    {
        $this->from($this->config->get('from.address'), $this->config->get('from.name'));
    }

    /**
     * @return \Mailgun\Message\MessageBuilder
     */
    public function builder()
    {
        return $this->builder;
    }

    /**
     * Get the message from MessageBuilder and apply custom/extra data
     *
     * @return array
     */
    public function getMessage()
    {
        $message = $this->builder->getMessage();
        if (!isset($message['from'])) {
            $this->setConfigFrom();
            $message = $this->builder->getMessage();
        }
        if(!isset($message['h:reply-to'])){
            $this->setConfigReplyTo();
            $message = $this->builder->getMessage();
        }
        if ($this->variables) {
            $message['recipient-variables'] = json_encode($this->variables);
        }
        return $message;
    }

}