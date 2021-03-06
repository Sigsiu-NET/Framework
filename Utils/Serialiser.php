<?php
/**
 * @package: Sobi Framework
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * @created Thu, Dec 1, 2016 12:03:39
 */

namespace Sobi\Utils;
defined( 'SOBI' ) || exit( 'Restricted access' );

use Sobi\Error\Exception;

class Serialiser
{
	/**
	 * @param string $var
	 * @param null $name
	 * @throws Exception
	 * @return mixed
	 */
	public static function Unserialise( $var, $name = null )
	{
		$r = null;
		if ( is_string( $var ) && strlen( $var ) > 2 ) {
			if ( ( $var2 = base64_decode( $var, true ) ) ) {
				if ( function_exists( 'gzinflate' ) ) {
					if ( ( $r = @gzinflate( $var2 ) ) ) {
						if ( !$r = @unserialize( $r ) ) {
							throw new Exception( sprintf( 'Cannot unserialize compressed variable %s', $name ) );
						}
					}
					else {
						if ( !( $r = @unserialize( $var2 ) ) ) {
							throw new Exception( sprintf( 'Cannot unserialize raw (?) encoded variable %s', $name ) );
						}
					}
				}
				else {
					if ( !( $r = @unserialize( $var2 ) ) ) {
						throw new Exception( sprintf( 'Cannot unserialize raw encoded variable %s', $name ) );
					}
				}
			}
			else {
				if ( !( $r = @unserialize( $var ) ) ) {
					throw new Exception( sprintf( 'Cannot unserialize raw variable %s', $name ) );
				}
			}
		}
		return $r;
	}

	/**
	 * @param mixed $var
	 * @return string
	 */
	public static function Serialise( $var )
	{
		if ( !( is_string( $var ) ) && ( is_array( $var ) && count( $var ) ) || is_object( $var ) ) {
			$var = serialize( $var );
		}
		if ( is_string( $var ) && function_exists( 'gzdeflate' ) && ( strlen( $var ) > 500 ) ) {
			$var = gzdeflate( $var, 9 );
		}
		if ( is_string( $var ) && strlen( $var ) > 2 ) {
			$var = base64_encode( $var );
		}
		return is_string( $var ) ? $var : null;
	}


	/**
	 * @param $data
	 * @param bool $force
	 * @return array|mixed
	 */
	public static function StructuralData( $data, $force = false )
	{
		if ( is_string( $data ) && strstr( $data, '://' ) ) {
			$struct = explode( '://', $data );
			switch ( $struct[ 0 ] ) {
				case 'json':
					if ( strstr( $struct[ 1 ], "':" ) || strstr( $struct[ 1 ], "{'" ) || strstr( $struct[ 1 ], "['" ) ) {
						$struct[ 1 ] = str_replace( "'", '"', $struct[ 1 ] );
					}
					$data = json_decode( $struct[ 1 ] );
					break;
				case 'serialized':
					if ( strstr( $struct[ 1 ], "':" ) || strstr( $struct[ 1 ], ":'" ) || strstr( $struct[ 1 ], "['" ) ) {
						$struct[ 1 ] = str_replace( "'", '"', $struct[ 1 ] );
					}
					$data = unserialize( $struct[ 1 ] );
					break;
				case 'csv':
					if ( function_exists( 'str_getcsv' ) ) {
						$data = str_getcsv( $struct[ 1 ] );
					}
					break;
			}
		}
		elseif ( is_string( $data ) && $force ) {
			if ( strstr( $data, '|' ) ) {
				$data = explode( '|', $data );
			}
			elseif ( strstr( $data, ',' ) ) {
				$data = explode( ',', $data );
			}
			elseif ( strstr( $data, ';' ) ) {
				$data = explode( ';', $data );
			}
			else {
				$data = [ $data ];
			}
		}
		return $data;
	}

	public static function Unserialize( $var, $name = null )
	{
		return self::Unserialise( $var, $name );
	}

	public static function Serialize( $var )
	{
		return self::Serialise( $var );
	}
}
