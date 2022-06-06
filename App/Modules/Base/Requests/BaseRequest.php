<?php
namespace App\Modules\Base\Requests;

use App\Exceptions\ValidateException;
use App\Handlers\Traits\MakeErrorMassageFormatter;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    use MakeErrorMassageFormatter;

    public function authorize()
    {
        return true;
    }

    protected function validationData()
    {
        return $this->input_value();
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidateException($this->errorMessageFormatter(
            $validator->failed(),
            $validator->errors()->messages(),
            $this->validationData()
        ));
    }
}