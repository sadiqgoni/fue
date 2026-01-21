<?php

namespace App\Livewire\Forms;

use App\Models\ActivityLog;
use App\Models\LocalGovt as LocalGovtModel;
use App\Models\State as StateModel;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class LocalGovt extends Component
{
    public $search, $perpage = 25;
    public $name, $state_id, $status = 1, $ids;
    use WithPagination, WithoutUrlPagination, LivewireAlert;

    public $edit = false, $create = false, $record = true;

    protected function rules()
    {
        return [
            'name' => 'required|regex:/^[\pL\s]+$/u',
            'state_id' => 'required',
            'status' => 'required',
        ];
    }

    public function create_lga()
    {
        $this->create = true;
        $this->edit = false;
        $this->record = false;
    }

    public function close()
    {
        $this->record = true;
        $this->create = false;
        $this->edit = false;
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->name = '';
        $this->state_id = '';
        $this->status = 1;
        $this->ids = '';
    }

    public function updated($pro)
    {
        $this->validateOnly($pro);
    }

    public function store()
    {
        $this->validate();
        $lga = new LocalGovt();
        $lga->name = $this->name;
        $lga->state_id = $this->state_id;
        $lga->status = $this->status;
        $lga->save();

        $this->resetFields();
        $this->alert('success', 'Local Government have been added');
        $user = Auth::user();
        $log = new ActivityLog();
        $log->user_id = $user->id;
        $log->action = "Added ($this->name) local government";
        $log->save();
    }

    public function edit_record($id)
    {
        $this->create = false;
        $this->edit = true;
        $this->record = false;
        $lga = LocalGovtModel::find($id);
        $this->ids = $id;
        $this->name = $lga->name;
        $this->state_id = $lga->state_id;
        $this->status = $lga->status;
    }

    public function update($id)
    {
        $this->validate();
        $lga = LocalGovtModel::find($id);
        $lga->name = $this->name;
        $lga->state_id = $this->state_id;
        $lga->status = $this->status;
        $lga->save();

        $this->close();
        $this->alert('success', 'Local Government have been updated');

        $user = Auth::user();
        $log = new ActivityLog();
        $log->user_id = $user->id;
        $log->action = "Updated ($this->name) local government";
        $log->save();
    }

    public function status_change($id)
    {
        $lga = LocalGovtModel::find($id);
        if ($lga->status == 1) {
            $lga->status = 0;
            $message = 'Local Government discontinued successfully';
        } else {
            $lga->status = 1;
            $message = 'Local Government activated successfully';
        }
        $lga->save();
        $this->alert('success', $message);
    }

    public function render()
    {
        $states = StateModel::active()->get();
        $lgas = LocalGovtModel::with('state')
            ->where('name', 'like', "%$this->search%")
            ->orWhereHas('state', function($query) {
                $query->where('name', 'like', "%$this->search%");
            })
            ->paginate($this->perpage);

        return view('livewire.forms.local-govt', compact('states', 'lgas'))->extends('components.layouts.app');
    }
}