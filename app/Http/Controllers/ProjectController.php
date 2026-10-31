<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('pages.projects', [
            'projects' => config('zenray.projects', []),
            'gallery' => config('zenray.gallery', []),
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless($project->is_active, 404);

        return view('pages.project', [
            'project' => $project,
            'more' => Project::query()->active()->ordered()->whereKeyNot($project->id)->limit(3)->get(),
        ]);
    }
}
