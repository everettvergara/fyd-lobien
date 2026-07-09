<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Services\ContentPageSyncService;
use App\Modules\PageManager\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Seeds Lobien Privacy Policy and Terms of Use from lobiengroup.com.
 *
 * @see contrib_themes/lobien/README.md
 */
class LobienLegalPagesSeeder extends Seeder
{
    private const SITE_NAME = 'Lobien Realty Group';

    private const ADDRESS = '23F High Street South Corporate Plaza, Tower 1, 26th Street Corner 9th Avenue, Bonifacio Global City, Taguig City, Philippines 1630';

    private const PHONE_PRIMARY = '+63 999 227 7125';

    private const PHONE_DIRECT = '+632 8983 9311';

    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@fyd.local')->first();

        if ($admin === null) {
            return;
        }

        $pageSync = app(ContentPageSyncService::class);

        $this->removeLegacyTermsOfService($pageSync);

        $pages = [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'summary' => 'How Lobien Realty Group collects, uses, and protects your personal information.',
                'body' => $this->privacyPolicyBody(),
                'seo_title' => 'Privacy Policy — '.self::SITE_NAME,
                'meta_description' => 'Read the Lobien Realty Group privacy policy.',
            ],
            [
                'slug' => 'terms-of-use',
                'title' => 'Terms of Use',
                'summary' => 'Terms and conditions for using the Lobien Realty Group website.',
                'body' => $this->termsOfUseBody(),
                'seo_title' => 'Terms of Use — '.self::SITE_NAME,
                'meta_description' => 'Read the Lobien Realty Group terms of use.',
            ],
        ];

        foreach ($pages as $data) {
            $content = Content::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'content_type' => 'page',
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'body' => $data['body'],
                    'status' => ContentStatus::Published,
                    'published_at' => now(),
                    'author_id' => $admin->id,
                ]
            );

            $content->saveSeo([
                'seo_title' => $data['seo_title'],
                'meta_description' => $data['meta_description'],
            ]);

            $pageSync->syncContentPage($content->fresh());
        }
    }

    protected function removeLegacyTermsOfService(ContentPageSyncService $pageSync): void
    {
        $legacy = Content::query()->where('slug', 'terms-of-service')->first();

        if ($legacy !== null) {
            $pageSync->removeContentPage($legacy);
            $legacy->forceDelete();
        }

        $legacyPage = Page::query()->where('path', '/terms-of-service')->first();

        if ($legacyPage !== null) {
            $legacyPage->blocks()->delete();
            $legacyPage->forceDelete();
        }
    }

    protected function privacyPolicyBody(): string
    {
        $site = self::SITE_NAME;
        $address = self::ADDRESS;
        $phonePrimary = self::PHONE_PRIMARY;
        $phoneDirect = self::PHONE_DIRECT;

        return <<<HTML
<p>{$site} ("we," "our," or "us") values your privacy and is committed to protecting your personal information. This Privacy Policy outlines how we collect, use, and safeguard your data when you visit our website.</p>

<h2>Information We Collect</h2>
<p><strong>Personal Information:</strong> When you contact us or place an order, we may collect personal details such as your name, email address, phone number, and shipping address.</p>
<p><strong>Non-Personal Information:</strong> We may collect non-identifiable data such as browser type, IP address, and pages visited to improve our website's functionality.</p>

<h2>How We Use Your Information</h2>
<ul>
<li>To process and fulfill your orders.</li>
<li>To communicate with you regarding your inquiries, orders, or updates.</li>
<li>To enhance our website and customer service.</li>
<li>To comply with legal obligations.</li>
</ul>

<h2>Information Sharing and Disclosure</h2>
<p>We do not sell, trade, or rent your personal information to third parties. We may share your data with:</p>
<ul>
<li><strong>Service Providers:</strong> Third-party vendors who assist in order processing, payment, and delivery.</li>
<li><strong>Legal Requirements:</strong> If required by law or to protect our rights and safety.</li>
</ul>

<h2>Data Security</h2>
<p>We implement appropriate security measures to protect your personal information from unauthorized access, alteration, or disclosure. However, no method of transmission over the Internet is completely secure.</p>

<h2>Your Rights</h2>
<p>You have the right to access, correct, or delete your personal information. To exercise these rights, please contact us at {$phonePrimary} and {$phoneDirect}.</p>

<h2>Cookies</h2>
<p>Our website uses cookies to enhance user experience. You can modify your browser settings to decline cookies, but this may affect website functionality.</p>

<h2>Third-Party Links</h2>
<p>Our website may contain links to third-party sites. We are not responsible for the privacy practices of these external sites.</p>

<h2>Changes to This Privacy Policy</h2>
<p>We may update this policy periodically. Any changes will be posted on this page with the revised effective date.</p>

<h2>Contact Us</h2>
<p>If you have any questions or concerns about this Privacy Policy, please contact us at:</p>
<p>
{$site}<br>
{$address}<br>
Call or Message us at {$phonePrimary}<br>
Direct Call at {$phoneDirect}
</p>

<p>By using our website, you consent to this Privacy Policy.</p>
HTML;
    }

    protected function termsOfUseBody(): string
    {
        $site = self::SITE_NAME;
        $address = self::ADDRESS;
        $phonePrimary = self::PHONE_PRIMARY;

        return <<<HTML
<p>Welcome to the {$site} website ("Site"). By accessing or using this Site, you agree to comply with and be bound by the following terms and conditions ("Terms of Use"). If you do not agree to these terms, please do not use the Site.</p>

<h2>Use of the Site</h2>
<p>You agree to use the Site for lawful purposes only. You are prohibited from using the Site to:</p>
<ul>
<li>Violate any local, national, or international law or regulation.</li>
<li>Transmit any harmful, defamatory, obscene, or otherwise objectionable content.</li>
<li>Attempt to gain unauthorized access to the Site's systems or networks.</li>
</ul>

<h2>Intellectual Property</h2>
<p>All content on this Site, including text, graphics, logos, images, and software, is the property of {$site} and is protected by applicable intellectual property laws. You may not reproduce, distribute, or create derivative works from any content on this Site without our prior written permission.</p>

<h2>User Submissions</h2>
<p>Any information, feedback, or materials you submit to the Site ("Submissions") will be considered non-confidential and non-proprietary. By submitting, you grant us a perpetual, royalty-free, worldwide license to use, reproduce, modify, and distribute your Submissions.</p>

<h2>Disclaimer of Warranties</h2>
<p>The Site and its content are provided "as is" without any warranties of any kind, either express or implied. We do not warrant that the Site will be uninterrupted, error-free, or free from viruses or other harmful components.</p>

<h2>Limitation of Liability</h2>
<p>To the fullest extent permitted by law, {$site} shall not be liable for any direct, indirect, incidental, consequential, or punitive damages arising from your use of the Site.</p>

<h2>Third-Party Links</h2>
<p>The Site may contain links to third-party websites for your convenience. We do not endorse or assume any responsibility for the content or practices of these external sites.</p>

<h2>Changes to Terms of Use</h2>
<p>We reserve the right to modify these Terms of Use at any time. Changes will be effective immediately upon posting on this page. Your continued use of the Site constitutes your acceptance of the revised terms.</p>

<h2>Governing Law</h2>
<p>These Terms of Use are governed by and construed in accordance with the laws of the Philippines. Any disputes arising from these terms shall be resolved in the courts of Taguig City, Philippines.</p>

<h2>Contact Us</h2>
<p>If you have any questions about these Terms of Use, please contact us at:</p>
<p>
{$address}<br>
Call or Message us at {$phonePrimary}
</p>

<p>By using this Site, you acknowledge that you have read, understood, and agree to be bound by these Terms of Use.</p>
HTML;
    }
}
