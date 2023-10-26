<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class LogoUploader extends Component
{
    use WithFileUploads;

    /**
     * @var boolean
     */
    public $uploaded = false;

    /**
     * @var TemporaryUploadedFile
     */
    public $image;

    /**
     * @var
     */
    public $oldImage;

    /**
     * @var array
     */
    protected $rules = [
        'image' => 'required|image|max:1024',
    ];

    /**
     * Save uploaded logo
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save()
    {
        try {
            $this->validate();
        } catch (ValidationException $exception) {
            session()->flash('error', $exception->getMessage());
            $this->image = false;
            return redirect()->back();
        }

        $user = Auth::user();

        if ($user->logo_file_path) {
            File::delete(storage_path('app') . '/' , $user->logo_file_path);
        }

        $fileName = $user->id . "---" . $user->name . "---companyLogo" . '.' . $this->image->getClientOriginalExtension();

        $path = $this->image->storeAs('logo', $fileName);

        $user->logo_file_name = $fileName;
        $user->logo_file_path = $path;
        $user->save();

        session()->flash('success', 'Logo has been successfully Uploaded.');

        $this->image = false;
        $this->oldImage = false;
        return redirect()->back();
    }

    /**
     * Mount the component
     */
    public function mount()
    {
        $user = Auth::user();

        if ($user->logo_file_path) {
            $content = base64_encode(file_get_contents(storage_path('app') . '/' . $user->logo_file_path));
            $this->uploaded = true;
            $this->oldImage = 'data: '. mime_content_type(storage_path('app') . '/' . $user->logo_file_path) .';base64,'. $content;;
        }
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.logo-uploader');
    }
}
