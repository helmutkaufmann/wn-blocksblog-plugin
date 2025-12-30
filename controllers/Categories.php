<?php namespace Mercator\BlocksBlog\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Request;
use Flash;
use Mercator\BlocksBlog\Models\Category;

class Categories extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        $this->addCss('/plugins/mercator/blocksblog/assets/backend.css');
        BackendMenu::setContext('mercator.blocksblog', 'blocksblog', 'categories');
    }

    public function index_onDelete()
    {
        $ids = $this->extractCheckedIds();

        if (!$ids) {
            Flash::warning('No categories selected.');
            return $this->listRefresh();
        }

        Category::whereIn('id', $ids)->delete();

        Flash::success('Selected categories deleted.');
        return $this->listRefresh();
    }

    /**
     * Extract checked IDs from Winter backend list bulk action requests.
     */
    protected function extractCheckedIds(): array
    {
        $keys = ['checked', 'checkedIds', 'checked_ids', 'ids', 'id'];

        $data = null;

        foreach ($keys as $k) {
            $v = post($k);
            if ($v !== null && $v !== '') {
                $data = $v;
                break;
            }
        }

        // Fallback: scan all request inputs for likely keys
        if ($data === null || $data === '' || $data === []) {
            foreach ((array) Request::all() as $k => $v) {
                $ks = (string) $k;
                if (stripos($ks, 'checked') !== false || stripos($ks, 'ids') !== false) {
                    if ($v !== null && $v !== '') {
                        $data = $v;
                        break;
                    }
                }
            }
        }

        $ids = [];

        if (is_array($data)) {
            $ids = $data;
        } elseif (is_string($data) && strlen($data) && $data[0] === '[') {
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                $ids = $decoded;
            }
        } elseif (is_string($data)) {
            // comma separated or single value
            $ids = preg_split('/\s*,\s*/', trim($data), -1, PREG_SPLIT_NO_EMPTY);
        }

        $ids = array_values(array_filter(array_map('intval', (array) $ids), function($i) {
            return $i > 0;
        }));

        return $ids;
    }



/**
 * AJAX handler wrapper for toolbar bulk delete.
 * Winter AJAX requires handler names like onX.
 */
public function onDelete()
{
    return $this->index_onDelete();
}

}
