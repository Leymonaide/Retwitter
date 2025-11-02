<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025 Leymonaide.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License 
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);
namespace Retwitter\Page\Base;

use Rehike\i18n\i18n;
use Rehike\i18n\Internal\Lang\NamespaceBoundLanguageApi;

class MFooter
{
    public string $copyright;

    /**
     * @var MFooterLink[]
     */
    public array $links = [];

    public function __construct(bool $forStream = false)
    {
        $i18n = i18n::getNamespace("footer");

        $this->copyright = $i18n->format("copyright_format", date("Y"));

        $this->links[] = new MFooterLink(
            label: $i18n->get("link_about"),
            url: "/about",
        );

        $this->links[] = new MFooterLink(
            label: $i18n->get("link_help"),
            url: "//support.twitter.com/",
        );

        if ($forStream)
        {
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_blog"),
                url: "//blog.twitter.com/",
            );
            
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_status"),
                url: "//status.twitter.com/",
            );
            
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_jobs"),
                url: "//about.twitter.com/careers",
            );
        }

        $this->links[] = new MFooterLink(
            label: $i18n->get("link_terms"),
            url: "/tos",
        );

        $this->links[] = new MFooterLink(
            label: $i18n->get("link_privacy"),
            url: "/privacy",
        );

        $this->links[] = new MFooterLink(
            label: $i18n->get("link_cookies"),
            url: "//support.twitter.com/articles/20170514",
        );

        $this->links[] = new MFooterLink(
            label: $i18n->get("link_ads_info"),
            url: "//support.twitter.com/articles/20170451",
        );

        if ($forStream)
        {
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_brand"),
                url: "//about.twitter.com/press/brand-assets",
            );
            
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_apps"),
                url: "//about.twitter.com/products",
            );
            
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_advertise"),
                url: "//ads.twitter.com/?ref=gl-tw-tw-twitter-advertise",
            );
            
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_marketing"),
                url: "//marketing.twitter.com/",
            );
            
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_businesses"),
                url: "//business.twitter.com/",
            );
            
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_developers"),
                url: "//dev.twitter.com/",
            );
            
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_directory"),
                url: "/i/directory/profiles",
            );
            
            $this->links[] = new MFooterLink(
                label: $i18n->get("link_settings"),
                url: "/settings/personalization",
            );
        }

        $this->links[] = new MFooterLink(
            label: $i18n->get("link_retwitter"),
            url: "//github.com/Leymonaide/Retwitter",
        );
    }
}