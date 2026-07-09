<?php

namespace App\Modules\WebForms\Database\Seeders;

use App\Modules\WebForms\Models\Webform;
use Illuminate\Database\Seeder;

class DemoContactWebformSeeder extends Seeder
{
    public function run(): void
    {
        Webform::updateOrCreate(
            ['slug' => 'contact-form'],
            [
                'name' => 'Contact Us',
                'description' => 'For general inquiries, fill out the details below:',
                'is_active' => true,
                'public_page_path' => null,
                'schema' => [
                    'fields' => [
                        [
                            'key' => 'name',
                            'type' => 'text',
                            'label' => 'Your Name',
                            'placeholder' => 'Your Name',
                            'help' => '',
                            'required' => true,
                            'options' => [],
                            'validation' => ['min' => null, 'max' => 255],
                        ],
                        [
                            'key' => 'email',
                            'type' => 'email',
                            'label' => 'Email Address',
                            'placeholder' => 'Email Address',
                            'help' => '',
                            'required' => true,
                            'options' => [],
                            'validation' => ['min' => null, 'max' => 255],
                        ],
                        [
                            'key' => 'mobile',
                            'type' => 'tel',
                            'label' => 'Mobile No.',
                            'placeholder' => 'Mobile No.',
                            'help' => '',
                            'required' => true,
                            'options' => [],
                            'validation' => ['min' => null, 'max' => 50],
                        ],
                        [
                            'key' => 'message',
                            'type' => 'textarea',
                            'label' => 'Message',
                            'placeholder' => 'Message',
                            'help' => '',
                            'required' => false,
                            'options' => [],
                            'validation' => ['min' => null, 'max' => 5000],
                        ],
                    ],
                    'settings' => [
                        'submit_label' => 'Send Message',
                        'success_message' => 'Thank you for contacting us.',
                        'redirect_url' => null,
                    ],
                ],
            ],
        );
    }
}
