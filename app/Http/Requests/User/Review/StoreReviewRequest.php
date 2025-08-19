<?php

namespace App\Http\Requests\User\Review;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Review;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Campos obligatorios básicos
            'reviewed_id' => [
                'required',
                'exists:users,id',
                Rule::notIn([auth()->id()]),
                Rule::unique('reviews')->where(function ($query) {
                    return $query->where('reviewer_id', auth()->id())
                                ->where('service_request_id', $this->service_request_id);
                })
            ],
            'service_request_id' => 'required|exists:service_requests,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|min:10|max:1000',
            
            // Nuevos campos opcionales
            'aspects' => 'nullable|array|min:1|max:4',
            'aspects.*' => [
                'required',
                Rule::in(array_keys(Review::ASPECTS))
            ],
            'aspects_ratings' => 'nullable|array',
            'aspects_ratings.*' => 'required|integer|between:1,5',
            'would_recommend' => 'required|boolean',
            
            // Campos administrativos (solo para admins)
            'status' => [
                'sometimes',
                Rule::in([Review::STATUS_PENDING, Review::STATUS_APPROVED, Review::STATUS_REJECTED])
            ],
            'is_featured' => 'sometimes|boolean',
            'admin_notes' => 'sometimes|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            // Mensajes para campos básicos
            'reviewed_id.required' => 'El usuario a evaluar es requerido.',
            'reviewed_id.exists' => 'El usuario a evaluar no existe.',
            'reviewed_id.not_in' => 'No puedes evaluarte a ti mismo.',
            'reviewed_id.unique' => 'Ya has evaluado a este usuario para este servicio.',
            
            'service_request_id.required' => 'La solicitud de servicio es requerida.',
            'service_request_id.exists' => 'La solicitud de servicio no existe.',
            
            'rating.required' => 'La calificación es requerida.',
            'rating.integer' => 'La calificación debe ser un número entero.',
            'rating.between' => 'La calificación debe estar entre 1 y 5.',
            
            'comment.required' => 'El comentario es requerido.',
            'comment.string' => 'El comentario debe ser texto.',
            'comment.min' => 'El comentario debe tener al menos 10 caracteres.',
            'comment.max' => 'El comentario no puede exceder 1000 caracteres.',
            
            // Mensajes para aspectos
            'aspects.array' => 'Los aspectos deben ser un arreglo.',
            'aspects.min' => 'Debes evaluar al menos un aspecto.',
            'aspects.max' => 'No puedes evaluar más de 4 aspectos.',
            'aspects.*.required' => 'Cada aspecto es requerido.',
            'aspects.*.in' => 'El aspecto seleccionado no es válido.',
            
            'aspects_ratings.array' => 'Las calificaciones de aspectos deben ser un arreglo.',
            'aspects_ratings.*.required' => 'La calificación del aspecto es requerida.',
            'aspects_ratings.*.integer' => 'La calificación del aspecto debe ser un número entero.',
            'aspects_ratings.*.between' => 'La calificación del aspecto debe estar entre 1 y 5.',
            
            'would_recommend.required' => 'Debes indicar si recomendarías al usuario.',
            'would_recommend.boolean' => 'La recomendación debe ser verdadero o falso.',
            
            // Mensajes administrativos
            'status.in' => 'El estado seleccionado no es válido.',
            'is_featured.boolean' => 'El campo destacado debe ser verdadero o falso.',
            'admin_notes.string' => 'Las notas administrativas deben ser texto.',
            'admin_notes.max' => 'Las notas administrativas no pueden exceder 500 caracteres.'
        ];
    }

    /**
     * Configurar los datos después de la validación
     */
    public function passedValidation(): void
    {
        // Asegurar que aspects y aspects_ratings coincidan
        if ($this->has('aspects') && $this->has('aspects_ratings')) {
            $aspects = $this->input('aspects', []);
            $aspectsRatings = $this->input('aspects_ratings', []);
            
            // Filtrar solo las calificaciones de aspectos válidos
            $validAspectsRatings = [];
            foreach ($aspects as $aspect) {
                if (isset($aspectsRatings[$aspect])) {
                    $validAspectsRatings[$aspect] = $aspectsRatings[$aspect];
                }
            }
            
            $this->merge([
                'aspects_ratings' => $validAspectsRatings
            ]);
        }
        
        // Agregar reviewer_id automáticamente
        $this->merge([
            'reviewer_id' => auth()->id()
        ]);
        
        // Si no se especifica estado, usar aprobado por defecto
        if (!$this->has('status')) {
            $this->merge([
                'status' => Review::STATUS_APPROVED
            ]);
        }
    }

    /**
     * Validaciones adicionales personalizadas
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que si se proporcionan aspectos, también se proporcionen sus calificaciones
            if ($this->has('aspects') && !$this->has('aspects_ratings')) {
                $validator->errors()->add('aspects_ratings', 'Debes proporcionar calificaciones para todos los aspectos evaluados.');
            }
            
            // Validar que las calificaciones de aspectos correspondan a los aspectos evaluados
            if ($this->has('aspects') && $this->has('aspects_ratings')) {
                $aspects = $this->input('aspects', []);
                $aspectsRatings = $this->input('aspects_ratings', []);
                
                foreach ($aspects as $aspect) {
                    if (!isset($aspectsRatings[$aspect])) {
                        $validator->errors()->add("aspects_ratings.{$aspect}", "Falta la calificación para el aspecto: {$aspect}");
                    }
                }
                
                // Validar que no haya calificaciones para aspectos no evaluados
                foreach (array_keys($aspectsRatings) as $aspect) {
                    if (!in_array($aspect, $aspects)) {
                        $validator->errors()->add("aspects_ratings.{$aspect}", "No puedes calificar un aspecto que no estás evaluando: {$aspect}");
                    }
                }
            }
            
            // Validar que el usuario tenga permisos para campos administrativos
            if (($this->has('status') || $this->has('is_featured') || $this->has('admin_notes')) && 
                !auth()->user()->hasRole('admin')) {
                $validator->errors()->add('authorization', 'No tienes permisos para modificar campos administrativos.');
            }
        });
    }
}
