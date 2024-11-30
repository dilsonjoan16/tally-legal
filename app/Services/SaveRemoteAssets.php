<?php
declare (strict_types = 1);

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SaveRemoteAssets
{
    /**
     * Invokes the service, saving a remote asset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $oldRecord
     * @param  string  $path
     * @return string
     */
    public function __invoke(Request $request, ?string $oldRecord, string $path, string $fieldName)
    {
        if ($oldRecord !== null) {
            Storage::disk('s3')->delete('assets/' . $oldRecord);
        }

        $avatar = $request->file($fieldName);
        $fileName = $avatar->getClientOriginalName() . '-' . Str::uuid();

        Storage::disk('s3')->putFileAs("assets/$path/$fileName", $avatar, $avatar->getClientOriginalName() . Str::uuid(), 'public');

        return $fileName;
    }
}
