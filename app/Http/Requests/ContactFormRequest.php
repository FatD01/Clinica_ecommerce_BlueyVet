<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Cambia a true para permitir que cualquier usuario (incluso no autenticado) envíe el formulario
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255', // Valida que sea un formato de email válido
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Por favor, introduce tu nombre.',
            'email.required' => 'Por favor, introduce tu dirección de correo electrónico.',
            'email.email' => 'Por favor, introduce una dirección de correo electrónico válida.',
            'message.required' => 'Por favor, escribe tu mensaje.',
        ];
    }
}