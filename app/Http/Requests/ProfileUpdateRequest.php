<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation()
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
        // Only tidy something that looks like a phone number; anything else
        // (letters) is left as typed so validation rejects it.
        if ($this->filled('phone') && preg_match('/^[\d\s+().-]+$/', (string) $this->input('phone'))) {
            // "+91 98765-43210" → "9876543210": keep digits only.
            $digits = preg_replace('/\D/', '', (string) $this->input('phone'));
            $this->merge(['phone' => strlen($digits) > 10 && str_starts_with($digits, '91') ? substr($digits, 2) : $digits]);
        }

        $govtIds = $this->input('govt_ids');
        if (is_array($govtIds)) {
            $filteredGovtIds = array_filter($govtIds, function($id) {
                return !empty($id['type']) && !empty($id['number']);
            });
            $this->merge([
                'govt_ids' => array_values($filteredGovtIds)
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid mobile number (digits only).',
            'dob.before' => 'Date of birth must be in the past.',
            'anniversary.before_or_equal' => 'Anniversary cannot be in the future.',
            'passport_expiry.after' => 'This passport has expired. Please enter a valid passport.',
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'regex:/^\d{7,15}$/', Rule::unique(User::class)->ignore($this->user()->id)],
            'dob' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'anniversary' => ['nullable', 'date', 'before_or_equal:today'],
            'passport_no' => ['nullable', 'string', 'max:255'],
            'passport_expiry' => ['nullable', 'date', 'after:today'],
            'passport_issuing_country' => ['nullable', 'string', 'max:255'],
            'govt_ids' => ['nullable', 'array'],
            'govt_ids.*.type' => ['required', 'string', 'in:Aadhaar Card,PAN Card,Driving License'],
            'govt_ids.*.number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $parts = explode('.', $attribute);
                    $index = $parts[1] ?? null;
                    if ($index !== null) {
                        $type = $this->input("govt_ids.$index.type");
                        if ($type === 'Aadhaar Card' && !preg_match('/^[2-9]{1}[0-9]{11}$/', $value)) {
                            $fail('The Aadhaar Card number must be 12 digits long and cannot start with 0 or 1.');
                        } elseif ($type === 'PAN Card' && !preg_match('/^[a-zA-Z]{5}[0-9]{4}[a-zA-Z]{1}$/', $value)) {
                            $fail('The PAN Card number format is invalid.');
                        } elseif ($type === 'Driving License' && !preg_match('/^(AP|AR|AS|BR|CG|GA|GJ|HR|HP|JH|KA|KL|MP|MH|MN|ML|MZ|NL|OD|PB|RJ|SK|TN|TS|TR|UP|UK|WB|AN|CH|DN|DD|DL|JK|LA|LD|PY|ap|ar|as|br|cg|ga|gj|hr|hp|jh|ka|kl|mp|mh|mn|ml|mz|nl|od|pb|rj|sk|tn|ts|tr|up|uk|wb|an|ch|dn|dd|dl|jk|la|ld|py)[0-9]{2}[ -]?[0-9]{11}$/', $value)) {
                            $fail('The Driving License number format is invalid. Ensure it starts with a valid state code.');
                        }
                    }
                },
            ],
            'address' => ['nullable', 'array'],
            'preferences' => ['nullable', 'array'],
            'notifications' => ['nullable', 'array'],
        ];
    }
}
