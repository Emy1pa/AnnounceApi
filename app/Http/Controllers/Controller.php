<?php

namespace App\Http\Controllers;
/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="ANNOUNCE API",
 *      description="L5 Swagger OpenApi description",
 *      @OA\Contact(
 *          email="souaouti@gmail.com",
 *      ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="https://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
