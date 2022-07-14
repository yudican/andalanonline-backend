<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Schema;
use Livewire\Component;


class CrudGenerator extends Component
{
    public $table;
    public $filename;
    public $modelname;
    public $folder_namespace = null;
    public $form_type;

    public $tables = [];
    public $columns = [];
    public $field = [];
    public $field_column = [];
    public $field_columns = [];
    public $have_richtext = false;
    public $have_multiple_input = false;
    public $prefix = 'tbl_';
    public function mount()
    {
        $table_name = 'Tables_in_' . config('database.database_name');
        $exlude_table = [$this->prefix . 'failed_jobs', $this->prefix . 'migrations', $this->prefix . 'password_resets', $this->prefix . 'permission_role', $this->prefix . 'permissions', $this->prefix . 'personal_access_tokens', $this->prefix . 'role_user', $this->prefix . 'roles', $this->prefix . 'sessions', $this->prefix . 'team_user', $this->prefix . 'teams', $this->prefix . 'hideable_columns', $this->prefix . 'menus', $this->prefix . 'menu_role'];
        $columns = Schema::getAllTables();
        foreach ($columns as $key => $value) {
            if (!in_array($value->$table_name, $exlude_table)) {
                $this->tables[] =  [
                    'name' => $value->$table_name
                ];
            }
        }
    }
    public function render()
    {
        if ($this->table) {
            if (count($this->columns) < 1) {
                $table = $this->prefix ? explode($this->prefix, $this->table)[1] : $this->table;
                $this->columns = Schema::getColumnListing($table);
                $fields = [];
                $labels = [];
                $this->columns = array_filter($this->columns, fn ($m) => !in_array($m, ['id', 'created_at', 'updated_at']));

                foreach ($this->columns as $key => $value) {
                    $fields[$value] = 'text';
                    $labels[$value] = ucwords(str_replace('_', ' ', $value));
                }
                $this->field = [
                    'type' => $fields,
                    'label' => $labels,
                ];
            }

            $this->columns = $this->columns;
            $this->field = $this->field;
            $this->field_column = array_merge_recursive($this->field['type'], $this->field['label']);
        }
        return view('livewire.crud-generator');
    }

    protected function getStub($type)
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    public function generate()
    {
        $field_columns = $this->_getFieldColumns();
        $controller_name = $this->filename . 'Controller';
        $view_name = str_replace('_', '-', $this->table);
        $view_name = str_replace('tbl-', '', $view_name);

        $controllerTemplate = $this->controllerTemplate($field_columns);
        $viewTemplate = $this->viewTemplate($field_columns);
        $modelTemplate = $this->modelTemplate($field_columns);
        $datatableTemplate = $this->viewDatatableTemplate($field_columns);

        $folder_namespace = $this->folder_namespace;
        $folder_namespace_lowertext = '';
        if ($folder_namespace) {
            $folder_namespace_lowertext = strtolower($this->folder_namespace);
        }

        if ($folder_namespace) {
            if (!is_dir(app_path("/Http/Livewire/" . $folder_namespace))) {
                mkdir(app_path("/Http/Livewire/" . $folder_namespace));
            }
        }

        if (!is_dir(app_path("/Http/Livewire/Table"))) {
            mkdir(app_path("/Http/Livewire/Table"));
        }

        if ($folder_namespace) {
            if (!is_dir(resource_path("/views/livewire/" . strtolower($this->folder_namespace)))) {
                mkdir(resource_path("/views/livewire/" . strtolower($this->folder_namespace)));
            }

            file_put_contents(app_path("/Http/Livewire/$folder_namespace/{$controller_name}.php"), $controllerTemplate);
            if ($this->form_type == 'modal') {
                file_put_contents(resource_path("/views/livewire/$folder_namespace_lowertext/{$view_name}.blade.php"), $viewTemplate);
            }

            if ($this->form_type == 'form') {
                file_put_contents(resource_path("/views/livewire/$folder_namespace_lowertext/{$view_name}.blade.php"), $viewTemplate);
            }
        } else {
            file_put_contents(app_path("/Http/Livewire/{$controller_name}.php"), $controllerTemplate);
            if ($this->form_type == 'modal') {
                file_put_contents(resource_path("/views/livewire/{$view_name}.blade.php"), $viewTemplate);
            }

            if ($this->form_type == 'form') {
                file_put_contents(resource_path("/views/livewire/{$view_name}.blade.php"), $viewTemplate);
            }
        }

        file_put_contents(app_path("/Models/{$this->filename}.php"), $modelTemplate);
        file_put_contents(app_path("/Http/Livewire/Table/{$this->filename}Table.php"), $datatableTemplate);

        // route path
        $this->generateRouteFile($controller_name, $view_name, $folder_namespace);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'CRUD Berhasil Dibuat']);
    }

    private function controllerTemplate($field_columns)
    {
        $view_name = str_replace('_', '-', $this->table);
        $view_name = str_replace('tbl-', '', $view_name);
        $last_character = substr($view_name, -1);
        $table_name = str_replace('tbl_', '', $this->table);
        if ($last_character == 's') {
            $view_name = substr($view_name, 0, -1);
        }

        $last_character_table = substr($table_name, -1);
        if ($last_character_table == 's') {
            $table_name = substr($table_name, 0, -1);
        }
        $controllerTemplate = str_replace(
            [
                '[modelName]',
                '[folderNamespace]',
                '[fileName]',
                '[tableId]',
                '[tableColumn]',
                '[tableFileColumn]',
                '[table_name]',
                '[viewName]',
                '[useForm]',
                '[useModal]',
                '[formRequest]',
                '[formRequestUpdate]',
                '[getTableId]',
                '[makeRules]',
                '[makeRulesFile]',
                '[getDataById]',
                '[resetForm]',
                '[folderNamespaceLower]',
                '[loadStorage]',
                '[loadFileUpload]',
                '[loadFileUploadInsert]',
                '[loadFileUploadUpdate]',
            ],
            [
                $this->filename,
                $this->folder_namespace ? "\\" . ucfirst($this->folder_namespace) : '',
                $this->filename,
                'public $' . $table_name . '_id',
                str_replace('<br>', '', implode(';' . PHP_EOL, $this->_getTableColumn())),
                str_replace('<br>', '', implode(';' . PHP_EOL, $this->_getFileTableColumn($field_columns))),
                $table_name,
                $view_name,
                $this->form_type == 'form' ? 'true' : 'false',
                $this->form_type == 'modal' ? 'true' : 'false',
                str_replace('<br>', '', implode(',' . PHP_EOL, $this->_getFormRequest($field_columns, 'insert'))),
                str_replace('<br>', '', implode(',' . PHP_EOL, $this->_getFormRequest($field_columns, 'update'))),
                '$this->' . $table_name . '_id',
                str_replace('<br>', '', implode(',' . PHP_EOL, $this->_makeRules($field_columns))),
                $this->_makeRulesFile($field_columns),
                str_replace('<br>', '', implode(';' . PHP_EOL, $this->_getDataById($table_name))),
                str_replace('<br>', '', implode(';' . PHP_EOL, $this->_resetForm($field_columns))),
                strtolower($this->folder_namespace ? $this->folder_namespace . '.' : ''),
                str_replace('<br>', '', implode(';' . PHP_EOL, $this->_getLoadStorage($field_columns))),
                $this->_getLoadFileUpload($field_columns),
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_getLoadFileUploadInsert($field_columns))),
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_getLoadFileUploadUpdate($field_columns))),
            ],
            $this->getStub('Controller')
        );

        return $controllerTemplate;
    }

    private function viewDatatableTemplate($field_columns)
    {
        $datatableTemplate = str_replace(
            [
                '[datatableColumn]',
                '[fileName]',
                '[table_name]',
            ],
            [
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_getDatatableColumn($field_columns))),
                $this->filename,
                $this->table,
            ],
            $this->getStub('Datatable')
        );

        return $datatableTemplate;
    }


    private function viewTemplate($field_columns)
    {
        $pieces = preg_split('/(?=[A-Z])/', $this->filename);
        $result = array_diff($pieces, ['']);

        $viewTemplate = str_replace(
            [
                '[formInput]',
                '[label]',
                '[assetRichText]',
                '[assetMultipleInput]',
                '[richText]',
                '[multipleInput]',
                '[itemLabel]',
                '[itemValue]',
                '[fileName]',
                '[datatableName]',
            ],
            [
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_makeFormInput($field_columns))),
                str_replace('_', '-', $this->table),
                $this->have_richtext ? '<script src="{{asset(\'assets/js/plugin/summernote/summernote-bs4.min.js\')}}"></script>' : null,
                $this->have_multiple_input ? '<script src="{{asset(\'assets/js/plugin/select2/select2.full.min.js\')}}"></script>' : null,
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_getRichText($field_columns))),
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_getMultipleInput($field_columns))),
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_getItemLabel($field_columns))),
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_getItemValue($field_columns))),
                str_replace('_', ' ', $this->table),
                strtolower(implode('-', $result))
            ],
            $this->getStub($this->form_type == 'modal' ? 'ViewModal' : 'View')
        );

        return $viewTemplate;
    }

    private function modelTemplate($field_columns)
    {
        $modelTemplate = str_replace(
            [
                '[fileName]',
                '[fillable]',
                '[dates]',
            ],
            [
                $this->filename,
                implode(',', $this->_getFillable($field_columns)),
                implode(',', $this->_getDates($field_columns)),
            ],
            $this->getStub('Model')
        );

        return $modelTemplate;
    }

    private function _getFillable($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            $column_render[] = "'$key'";
        }
        return $column_render;
    }

    private function _getDates($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            if ($value['type'] == 'date') {
                $column_render[] = "'$key'";
            }
        }
        return $column_render;
    }

    private function _makeFormInput($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['richtext'])) {
                $this->have_richtext = true;
            }
            if (in_array($value['type'], ['multiple'])) {
                $this->have_multiple_input = true;
            }

            if (in_array($value['type'], ['textarea'])) {
                $column_render[] = '<x-textarea type="' . $value['type'] . '" name="' . $key . '" label="' . $value['label'] . '" />';
            }

            if (in_array($value['type'], ['richtext'])) {
                $column_render[] = '<div wire:ignore class="form-group @error(\'' . $key . '\')has-error has-feedback @enderror">
                                <label for="' . $key . '" class="text-capitalize">' . $value['label'] . '</label>
                                <textarea wire:model="' . $key . '" id="' . $key . '" class="form-control"></textarea>
                                @error(\'' . $key . '\')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>';
            }

            if (in_array($value['type'], ['text', 'number', 'hidden', 'date', 'password'])) {
                $column_render[] = '<x-text-field type="' . $value['type'] . '" name="' . $key . '" label="' . $value['label'] . '" />';
            }

            if ($value['type'] == 'select') {
                $column_render[] = '<x-select name="' . $key . '" label="' . $value['label'] . '" ><option value="">Select ' . $value['label'] . '</option></x-select>';
            }

            if ($value['type'] == 'multiple') {
                $column_render[] = '<x-select name="' . $key . '" label="' . $value['label'] . '" multiple ignore id="' . $key . '"><option value="">Select ' . $value['label'] . '</option></x-select>';
            }

            if (in_array($value['type'], ['image'])) {
                $column_render[] = '<x-input-photo foto="{{$' . $key . '}}" path="{{optional($' . $key . '_path)->temporaryUrl()}}"
                            name="' . $key . '_path"  label="' . $value['label'] . '" />';
            }
            if (in_array($value['type'], ['file'])) {
                $column_render[] = '<x-input-file file="{{$' . $key . '}}" path="{{optional($' . $key . '_path)->getClientOriginalName()}}" name="' . $key . '_path"  label="' . $value['label'] . '" />';
            }
        }

        return $column_render;
    }

    private function _getItemLabel($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            $column_render[] = '<td>' . $value['label'] . '</td>';
        }
        return $column_render;
    }

    private function _getItemValue($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            $column_render[] = '<td>{{ $item->' . $key . ' }}</td>';
        }
        return $column_render;
    }

    private function _getRichText($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['richtext'])) {
                $column_render[] = "$('#$key').summernote({
            placeholder: '$key',
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
            tabsize: 2,
            height: 300,
            callbacks: {
                        onChange: function(contents, \$editable) {
                            @this.set('$key', contents);
                        }
                    }
            });";
            }
        }
        return $column_render;
    }

    private function _getMultipleInput($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['multiple'])) {
                $column_render[] = "$('#$key').select2({
                    theme: 'bootstrap,
                });
                
                $('#$key').on('change', function (e) {
                    let data = $(this).val();
                    console.log(data)
                    @this.set('$key', data);
                });";
            }
        }
        return $column_render;
    }

    private function _getTableColumn()
    {
        $column_render = [];
        foreach ($this->columns as $column) {
            $column_render[] = 'public $' . $column;
        }

        return $column_render;
    }

    private function _getFileTableColumn($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['image', 'file'])) {
                $column_render[] = 'public $' . $key . '_path;';
            }
        }

        return $column_render;
    }

    private function _getLoadStorage($field_columns)
    {
        $column_render = [];
        $no = 0;
        foreach ($field_columns as $key => $value) {
            if ($no < 1) {
                if (in_array($value['type'], ['image', 'file'])) {
                    $column_render[] = 'use Illuminate\Support\Facades\Storage';
                    $column_render[] = 'use Livewire\WithFileUploads;';
                }
            }
        }
        return $column_render;
    }

    private function _getLoadFileUpload($field_columns)
    {
        $no = 0;
        foreach ($field_columns as $key => $value) {
            if ($no < 1) {
                if (in_array($value['type'], ['image', 'file'])) {
                    return 'use WithFileUploads;';
                }
            }
        }
        return '';
    }

    private function _getLoadFileUploadInsert($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['image', 'file'])) {
                $column_render[] = '$' . $key . ' = $this->' . $key . '_path->store(\'upload\', \'public\');';
            }
        }

        return $column_render;
    }

    private function _getLoadFileUploadUpdate($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['image', 'file'])) {
                $column_render[] = '
                    if ($this->' . $key . '_path) {
                        $' . $key . ' = $this->' . $key . '_path->store(\'upload\', \'public\');
                        $data = [\'' . $key . '\' => $' . $key . '];
                        if (Storage::exists(\'public/\' . $this->' . $key . ')) {
                            Storage::delete(\'public/\' . $this->' . $key . ');
                        }
                    }';
            }
        }

        return $column_render;
    }

    private function _getFormRequest($field_columns, $type)
    {
        $form_render = [];
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['image', 'file']) && $type == 'insert') {
                $form_render[] = '\'' . $key . '\'  => $' . $key . '';
            } else {
                $form_render[] = '\'' . $key . '\'  => $this->' . $key . '';
            }
        }

        return $form_render;
    }


    private function _getDatatableColumn($field_columns)
    {
        $column_render = ["Column::name('id')->label('No.'),"];
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['image'])) {
                $column_render[] = 'Column::callback([\'' . $key . '\'], function ($image) {
                return view(\'livewire.components.photo\', [
                    \'image_url\' => asset(\'storage/\' . $image),
                ]);
            })->label(__(\'' . $value['label'] . '\')),';
            } else if (in_array($value['type'], ['file'])) {
                $column_render[] = 'Column::callback([\'' . $key . '\'], function ($file) {
                return \'<a href="{{asset(\'storage/\' . $file)}}">show file</a>\';
            })->label(__(\'' . $value['label'] . '\')),';
            } else {
                $column_render[] = 'Column::name(\'' . $key . '\')->label(\'' . $value['label'] . '\')->searchable(),';
            }
        }

        return $column_render;
    }

    private function _makeRules($field_columns)
    {
        $rules = [];
        foreach ($field_columns as $key => $value) {
            if (!in_array($value['type'], ['image', 'file'])) {
                $rules[] = '\'' . $key . '\'  => \'required\'';
            }
        }

        return $rules;
    }
    private function _makeRulesFile($field_columns)
    {
        $rule = '';
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['image', 'file'])) {
                $path = '$rule[\'' . $key . '_path' . '\'] = \'required\';';
                $rule = 'if(!$this->update_mode){' . $path . '}';
            }
        }

        return $rule;
    }

    private function _getDataById($table_name)
    {
        $rules = [];
        foreach ($this->columns as $column) {

            $rules[] = '$this->' . $column . ' = $row->' . $column . '';
        }

        return $rules;
    }

    private function _resetForm($field_columns)
    {
        $reset_form = [];

        foreach ($field_columns as $key => $value) {
            if (!in_array($value['type'], ['image', 'file'])) {
                $reset_form[] = '$this->' . $key . ' = null';
            } else {
                $reset_form[] = '$this->' . $key . '_path' . ' = null';
            }
        }

        return $reset_form;
    }

    private function _getFieldColumns()
    {
        $field_column = [];
        foreach ($this->field_column as $key => $value) {
            $mergered = [];
            for ($i = 0; $i < count($value); $i++) {
                if ($i == 0) {
                    $mergered['type'] = $value[$i];
                }
                if ($i == 1) {
                    $mergered['label'] = ucwords($value[$i]);
                }
            }

            $field_column[$key] = $mergered;
            $mergered = [];
        }
        return $field_column;
    }

    private function delete($key, $value)
    {
        unset($this->columns[$key]);
        unset($this->field['type'][$value]);
        unset($this->field['label'][$value]);
    }

    private function _reset()
    {
        $this->table = null;
        $this->filename = null;
        $this->modelname = null;
        $this->form_type = null;
        $this->folder_namespace = '';

        $this->columns = [];
        $this->field = [];
        $this->field_column = [];
        $this->field_columns = [];
        $this->have_richtext = false;
        $this->have_multiple_input = false;
    }

    // generate_route_file
    private function generateRouteFile($controller_name, $view_name, $folder_namespace = null)
    {
        $file_path = base_path('routes/web.php');
        $data = file_get_contents($file_path);
        $route_name = str_replace('.', '-', $view_name);
        $import_file = "use App\\Http\\Livewire\\{$controller_name};";
        if ($folder_namespace) {
            $import_file = "use App\\Http\\Livewire\\{$folder_namespace}\\{$controller_name};";
        }

        $route_component = "Route::get('/{$view_name}', {$controller_name}::class)->name('{$route_name}');";
        $read_file_path = str_replace($import_file, '', $data);
        $read_file_path = str_replace($route_component, '', $read_file_path);
        $route_import_path = $import_file . PHP_EOL;
        $route_import_path .= "// [route_import_path]";
        $route_file = $route_component . PHP_EOL;
        $route_file .= "// [route_path]";

        $replace_route_path = str_replace('// [route_import_path]', $route_import_path, $read_file_path);
        $replace_file_path = str_replace('// [route_path]', $route_file, $replace_route_path);
        file_put_contents($file_path, $replace_file_path);
    }

    public function getTableName($name)
    {
        $filename = str_replace('_', '-', $name);
        $filename = str_replace('tbl-', '', $filename);
        $filename = implode('-', array_map('ucfirst', explode('-', $filename)));

        // remove last character
        $last_character = substr($filename, -1);
        if ($last_character == 's') {
            $filename = substr($filename, 0, -1);
        }

        $this->columns = [];
        $this->field = [];
        $this->field_column = [];
        $this->field_columns = [];
        $this->have_richtext = false;
        $this->have_multiple_input = false;
        $this->filename = $filename;
    }
}
