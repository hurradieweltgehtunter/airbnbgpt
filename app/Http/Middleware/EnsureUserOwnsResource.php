<?php
/**
 * Middleware to ensure, a user has only access to resources belongig to him.
 * use withh
 * Route::middleware(['owns-resource:App\Models\Housing,<foreign_key>'])->group(function () {
 *  // Routes here
 * });
 *
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;

class EnsureUserOwnsResource
{
    public function handle(Request $request, Closure $next, $modelClass, $foreignKey = 'user_id')
    {
        $model = $request->route()->parameter(lcfirst(class_basename($modelClass)));

        if($model && !is_object($model)) {
            $model = $modelClass::findOrFail($model);
        }

        if ($model && $request->user()->id !== $model->$foreignKey) {
            // Nicht autorisiert
            return redirect(RouteServiceProvider::HOME)->withErrors('You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
