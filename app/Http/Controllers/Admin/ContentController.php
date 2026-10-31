<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminContent;
use App\Support\ContentForm;
use App\Support\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * List, create, edit, reorder and delete any resource registered in
 * App\Support\AdminContent (hero slides, products, services, gallery…).
 */
class ContentController extends Controller
{
    public function index(Request $request, string $resource): View
    {
        $config = $this->config($resource);
        $search = trim($request->string('q')->toString());
        $titleColumn = $config['list']['title'];

        $items = $this->query($config)
            ->when($config['with'] ?? null, fn ($q, $with) => $q->with($with))
            ->when($config['list']['count'] ?? null, fn ($q, $count) => $q->withCount($count[0]))
            ->when($search !== '', fn ($q) => $q->where($titleColumn, 'like', "%{$search}%"))
            ->paginate(25)
            ->withQueryString();

        return view('admin.content.index', compact('resource', 'config', 'items', 'search'));
    }

    public function create(string $resource): View
    {
        $config = $this->config($resource);

        return view('admin.content.form', ['resource' => $resource, 'config' => $config, 'item' => new $config['model']]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $config = $this->config($resource);

        ContentForm::fill($request, new $config['model'], $config['fields'], $resource)->save();

        return to_route('admin.content.index', $resource)->with('status', "{$config['singular']} added.");
    }

    public function edit(string $resource, int $id): View
    {
        $config = $this->config($resource);

        return view('admin.content.form', ['resource' => $resource, 'config' => $config, 'item' => $config['model']::findOrFail($id)]);
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $config = $this->config($resource);

        ContentForm::fill($request, $config['model']::findOrFail($id), $config['fields'], $resource)->save();

        return to_route('admin.content.index', $resource)->with('status', "{$config['singular']} saved.");
    }

    /**
     * Swap an item with its neighbour in the display order.
     */
    public function move(Request $request, string $resource, int $id): RedirectResponse
    {
        $config = $this->config($resource);
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        $items = $this->query($config)->get(['id', 'sort_order'])->values();
        $index = $items->search(fn (Model $item) => $item->getKey() === $id);
        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index !== false && $items->has($target)) {
            // Renumber everything so equal or missing values never get stuck.
            $ids = $items->pluck('id')->all();
            [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];

            foreach ($ids as $position => $itemId) {
                $config['model']::whereKey($itemId)->first()?->forceFill(['sort_order' => $position + 1])->save();
            }
        }

        return back();
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $config = $this->config($resource);
        $item = $config['model']::findOrFail($id);

        $item->delete();
        Photo::forget($item->photo ?? null);

        return to_route('admin.content.index', $resource)->with('status', "{$config['singular']} deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function config(string $resource): array
    {
        return AdminContent::find($resource) ?? abort(404);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function query(array $config): Builder
    {
        return $config['model']::query()->orderBy('sort_order')->orderBy('id');
    }
}
