<?php namespace Naraki\Media\Jobs;

use Naraki\Core\Job;
use Naraki\Core\Models\Entity;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Naraki\Media\Facades\Media as MediaProvider;
use Naraki\Media\Models\Media;
use Naraki\Media\Support\SimpleUploadedImage;

class ProcessAvatar extends Job
{
    public $queue = 'db';

    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $username;

    /**
     *
     * @param string $url
     * @param string $username
     */
    public function __construct(string $url, string $username)
    {
        $this->url = $url;
        $this->username = $username;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();
        try {
            $this->processAvatar();
        } catch (\Exception $e) {
            \Log::critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
//            app('bugsnag')->notifyException($e, ['mailData'=>$this->email->getData()], "error");
        }
        $this->delete();
    }

    public function processAvatar()
    {
        if (!is_null($this->url) && !empty($this->url)) {
            $contentTypes = [
                'image/gif' => 'gif',
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
            ];
            $url = vsprintf('%s://%s%s', parse_url($this->url));
            $fileStream = null;
            try {
                $client = new Client();
                $response = $client->get($url);
                $status = $response->getStatusCode();
                $headers = $response->getHeaders();
                if ($status === 200) {
                    if (isset($headers['Content-Type'])) {
                        if (isset($contentTypes[$headers['Content-Type'][0]])) {
                            $avatarInfo = $this->makeSimpleImage($contentTypes[$headers['Content-Type'][0]])
                                ->cropAvatarFromStream($response->getBody());
                        }
                    }
                }
                unset($response);
            } catch (\Exception $e) {
                Log::critical('Error in Job ProcessAvatar',
                    [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
            }
        } else {
            $avatarInfo = $this->makeGeneratedAvatar()->processAvatar();
        }
        MediaProvider::image()->saveAvatar($avatarInfo);
    }

    public function makeGeneratedAvatar()
    {
        return new \Naraki\Media\Support\GeneratedAvatar(
            $this->username,
            $this->username,
            Entity::USERS,
            Media::IMAGE_AVATAR
        );
    }

    public function makeSimpleImage($extension)
    {
        return new SimpleUploadedImage(
            $this->username,
            $this->username,
            Entity::USERS,
            Media::IMAGE_AVATAR,
            $extension,
            sprintf('%s_%s', $this->username, makeHexUuid())
        );

    }

    public function __get($value)
    {
        return $this->{$value};
    }

}