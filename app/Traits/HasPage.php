<?php

namespace App\Traits;

use App\Models\Page;
use App\Models\PageContent;

trait HasPage
{
    public function pages()
    {
        return $this->morphMany(Page::class, 'pageable');
    }

    public function activePage()
    {
        return $this->pages()->active()->first();
    }

    public function savePage(array $data)
    {
        return $this->pages()->updateOrCreate(
            ['pageable_id' => $this->id, 'pageable_type' => self::class],
            $data
        );
    }

    public function pageContents()
    {
        return $this->morphMany(PageContent::class, 'pageable');
    }
}