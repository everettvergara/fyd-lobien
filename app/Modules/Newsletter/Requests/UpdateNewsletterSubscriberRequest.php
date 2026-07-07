<?php

namespace App\Modules\Newsletter\Requests;

use App\Modules\Newsletter\Models\NewsletterSubscriber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $subscriber = $this->route('newsletter_subscriber');

        return $subscriber instanceof NewsletterSubscriber
            && ($this->user()?->can('update', $subscriber) ?? false);
    }

    public function rules(): array
    {
        /** @var NewsletterSubscriber $subscriber */
        $subscriber = $this->route('newsletter_subscriber');

        return [
            'newsletter_list_id' => ['required', Rule::exists('newsletter_lists', 'id')],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('newsletter_subscribers', 'email')
                    ->where(fn ($query) => $query->where('newsletter_list_id', $this->input('newsletter_list_id')))
                    ->ignore($subscriber->id),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([NewsletterSubscriber::STATUS_ACTIVE, NewsletterSubscriber::STATUS_UNSUBSCRIBED])],
        ];
    }
}
