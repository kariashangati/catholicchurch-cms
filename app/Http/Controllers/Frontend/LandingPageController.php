<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\Frontend\FrontendContentService;
use Illuminate\Http\Request;
use App\Models\ContactMessage;

class LandingPageController extends Controller
{
    public function __construct(private FrontendContentService $frontend)
    {
    }

    public function home()
    {
        return view('frontend.pages.home', array_merge(
            $this->frontend->shared(),
            $this->frontend->home(),
            ['frontendService' => $this->frontend]
        ));
    }

public function kandas()
{
    return view('frontend.pages.kandas', $this->frontend->kandasPage());
}

 public function jumuiyas()
{
    return view('frontend.pages.jumuiyas', $this->frontend->jumuiyasPage());
}

  public function masses()
{
    return view('frontend.pages.masses', array_merge(
        $this->frontend->shared(),
        $this->frontend->massesPage()
    ));
}

public function announcements()
{
    return view('frontend.pages.announcements', array_merge(
        $this->frontend->shared(),
        $this->frontend->announcementsPage()
    ));
}

public function announcement(string $slug)
{
    $announcement = $this->frontend->announcementBySlug($slug);

    abort_unless($announcement, 404);

    return view('frontend.pages.announcement-show', array_merge(
        $this->frontend->shared(),
        [
            'announcement' => $announcement,
            'relatedAnnouncements' => $this->frontend->relatedAnnouncements($announcement, 3),
        ]
    ));
}

   public function projects()
{
    return view('frontend.pages.projects', array_merge(
        $this->frontend->shared(),
        $this->frontend->projectsPage(),
        ['frontendService' => $this->frontend]
    ));
}

public function giving()
{
    return view('frontend.pages.giving', array_merge(
        $this->frontend->shared(),
        $this->frontend->givingPage()
    ));
}

    public function ministries()
    {
        return view('frontend.pages.ministries', array_merge($this->frontend->shared(), [
            'items' => $this->frontend->ministries(),
        ]));
    }

 public function leadership()
{
    return view('frontend.pages.leadership', array_merge(
        $this->frontend->shared(),
        $this->frontend->leadershipPage()
    ));
}

 

    public function gallery()
{
    return view('frontend.pages.gallery', array_merge(
        $this->frontend->shared(),
        $this->frontend->galleriesPage()
    ));
}

public function contact()
{
    return view('frontend.pages.contact', $this->frontend->contactPage());
}



public function submitContact(Request $request)
{
    $validated = $request->validate([
        'full_name' => ['required', 'string', 'max:255'],
        'phone' => ['nullable', 'string', 'max:50'],
        'email' => ['nullable', 'email', 'max:255'],
        'contact_reason_id' => ['required', 'exists:contact_reasons,id'],
        'kanda_id' => ['nullable', 'exists:kandas,id'],
        'jumuiya_id' => ['nullable', 'exists:jumuiyas,id'],
        'message' => ['required', 'string'],
    ]);

    ContactMessage::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'Message sent successfully.',
    ]);
}

public function history()
{
    return view('frontend.pages.history', array_merge(
        $this->frontend->shared(),
        $this->frontend->historiesPage()
    ));
}

public function historyShow(string $slug)
{
    $history = $this->frontend->historyBySlug($slug);

    abort_unless($history, 404);

    return view('frontend.pages.history-show', array_merge(
        $this->frontend->shared(),
        [
            'history' => $history,
            'relatedHistories' => $this->frontend->relatedHistories($history, 3),
        ]
    ));
}

    public function page(string $slug)
    {
        $page = $this->frontend->pageBySlug($slug);
        abort_unless($page, 404);

        return view('frontend.pages.page', array_merge(
            $this->frontend->shared(),
            compact('page')
        ));
    }
}
