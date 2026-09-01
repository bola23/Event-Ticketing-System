<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesMediaUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryPhotoRequest;
use App\Models\Event;
use App\Models\GalleryPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GalleryPhotoController extends Controller
{
    use HandlesMediaUploads;

    public function index(Event $event): View
    {
        return view('admin.gallery-photos.index', ['event' => $event, 'galleryPhotos' => $event->galleryPhotos]);
    }

    public function create(Event $event): View
    {
        return view('admin.gallery-photos.form', ['event' => $event, 'galleryPhoto' => new GalleryPhoto]);
    }

    public function store(GalleryPhotoRequest $request, Event $event): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data = $this->withUploadedMedia($data, $request, 'image', 'image_path', 'gallery');

        $event->galleryPhotos()->create($data);

        return redirect()->route('admin.events.gallery-photos.index', $event);
    }

    public function edit(Event $event, GalleryPhoto $galleryPhoto): View
    {
        $this->assertBelongsToEvent($event, $galleryPhoto);

        return view('admin.gallery-photos.form', ['event' => $event, 'galleryPhoto' => $galleryPhoto]);
    }

    public function update(GalleryPhotoRequest $request, Event $event, GalleryPhoto $galleryPhoto): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $galleryPhoto);
        $data = $request->safe()->except(['image']);
        $data = $this->withUploadedMedia($data, $request, 'image', 'image_path', 'gallery', $galleryPhoto->image_path);

        $galleryPhoto->update($data);

        return redirect()->route('admin.events.gallery-photos.index', $event);
    }

    public function destroy(Event $event, GalleryPhoto $galleryPhoto): RedirectResponse
    {
        $this->assertBelongsToEvent($event, $galleryPhoto);
        $this->deleteStoredMedia($galleryPhoto->image_path);
        $galleryPhoto->delete();

        return redirect()->route('admin.events.gallery-photos.index', $event);
    }

    private function assertBelongsToEvent(Event $event, GalleryPhoto $galleryPhoto): void
    {
        if ($galleryPhoto->event_id !== $event->id) {
            throw new NotFoundHttpException;
        }
    }
}
