<?php

namespace App\Observers;

use App\Contracts\ContentGatewayInterface;
use App\Tattoo;
use App\Registry\ContentGatewayRegistry;
use App\Repositories\ImageRepository;

/**
 * Class SocialGalleryObserver
 * @package App\Observers
 */
class SocialGalleryObserver
{

    /**
     * @var \App\Repositories\ImageRepository
     */
    public $imageRepository;
    /**
     * @var ContentGatewayRegistry
     */
    private $contentGatewayRegistry;

    /**
     * SocialGalleryObserver constructor.
     *
     * @param \App\Repositories\ImageRepository $imageRepository
     * @param ContentGatewayRegistry $contentGatewayRegistry
     */
    public function __construct(ImageRepository $imageRepository, ContentGatewayRegistry $contentGatewayRegistry)
    {
        $this->imageRepository = $imageRepository;
        $this->contentGatewayRegistry = $contentGatewayRegistry;
    }

    /**
     * @param Tattoo $tattoo
     */
    public function deleted(Tattoo $tattoo)
    {
        $this->imageRepository->imageDelete($tattoo, 'image');
    }

    /**
     * @param Tattoo $tattoo
     */
    public function created(Tattoo $tattoo)
    {
        if (null !== $tattoo->published_at)
        {
            $channels = $this->contentGatewayRegistry->all();
            /** @var ContentGatewayInterface $channel */
            foreach ($channels as $channel) {
                $channel->publish($tattoo);
            }
        }
    }

    /**
     * @param Tattoo $tattoo
     */
    public function updated(Tattoo $tattoo)
    {
        if (null !== $tattoo->published_at && $tattoo->isDirty('published_at'))
        {
            $channels = $this->contentGatewayRegistry->all();
            /** @var ContentGatewayInterface $channel */
            foreach ($channels as $channel) {
                $channel->publish($tattoo);
            }
        }
    }
}