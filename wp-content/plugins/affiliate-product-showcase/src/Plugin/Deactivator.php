<?php

namespace AffiliateProductShowcase\Plugin;

final class Deactivator {
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
