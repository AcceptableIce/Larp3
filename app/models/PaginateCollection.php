<?
use Illuminate\Database\Eloquent\Collection;
class PaginateCollection extends Collection {
	public function paginate($perPage) {
	    $pagination = App::make('paginator');
	    $count = $this->count();
	    $page = $pagination->getCurrentPage($count);
	    $items = $this->slice(($page - 1) * $perPage, $perPage)->all();
	    $pagination = $pagination->make($items, $count, $perPage);
	    return $pagination;
	}
}