<?php
/**
 * Hooks for Mailgun extension for Mediawiki
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @author Tony Thomas <01tonythomas@gmail.com>
 * @license GPL-2.0+
 * @ingroup Extensions
*/

class MailgunHooks {
	/**
	 * Function to be run on startup in $wgExtensionFunctions
	 */
	public static function onRegistration() {
		if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
			require_once __DIR__ . '/vendor/autoload.php';
		}
	}

	/**
	 * Send a mail using Mailgun API
	 *
	 * @param array $headers
	 * @param array $to
	 * @param MailAddress $from
	 * @param string $subject
	 * @param string $body
	 * @return bool
	 */
	public static function onAlternateUserMailer(
		array $headers, array $to, MailAddress $from, $subject, $body
	) {
		$conf = RequestContext::getMain()->getConfig();
		$mailgunTransport = new \Mailgun\Mailgun( $conf->get( 'MailgunAPIKey' ) );
		$message = $mailgunTransport->BatchMessage( $conf->get( 'MailgunDomain' ) );

		$message->setFromAddress( $from );
		$message->setSubject( $subject );
		$message->setTextBody( $body );

		foreach( $headers as $headerName => $headerValue ) {
			$message->addCustomHeader( $headerName, $headerValue );
		}

		foreach( $to as $recip ) {
			try {
				$message->addToRecipient( $recip );
			} catch( Exception $e ) {
				return $e->getMessage();
			}
		}

		try {
			$message->finalize();
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return false;
	}
}