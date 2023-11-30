<?php
/**
 * Middleware to ensure, a user has only access to sub-resources belongig to him.
 * Example: Image belongs housing, housing belongs to user. User can only access images of housings he owns.
 *
 * use withh
 * Route::middleware(['owns-subresource:{ModelClass},{parentModelClass}'])->group(function () {
 *  // Routes here
 * });
 *
 * Route::prefix('images')->name('housingimages.')->middleware(['auth', 'verified', 'owns-subresource:App\Models\HousingImage,App\Models\Housing'])->group(function () {
 *
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserOwnsSubresource
{
    public function handle(Request $request, Closure $next, $modelClass, $parentModelClass, $foreign_key = 'user_id')
    {
        $className = class_basename($modelClass);
        $parentClassName = class_basename($parentModelClass);

        $model = $request->route()->parameter(lcfirst($className));

        // Überprüfen, ob das Modell geladen wurde und ob es sich um ein Objekt handelt
        if ($model && !is_object($model)) {
            $model = $modelClass::findOrFail($model);
        }

        // Überprüfen, ob das Bild zu einem Housing gehört, das dem Benutzer gehört
        if ($model && $model->$parentClassName && $request->user()->id !== $model->$parentClassName->$foreign_key) {
            // send a 403 Forbidden response
            abort(403, 'You are not allowed to access this resource.');
        }

        return $next($request);
    }
}
