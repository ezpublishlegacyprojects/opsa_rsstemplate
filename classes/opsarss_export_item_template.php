<?php
//
// Created on: <16-Jan-2010 08:05:08 Socrattes>
//
// SOFTWARE NAME: OPSA RSS Template Extension
// SOFTWARE RELEASE: 1.0
// BUILD VERSION:
// COPYRIGHT NOTICE: Copyright (C) Organizacion Publicitaria S.A.
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//

/*!
 \class opsaRSSExportItemTemplate
 \brief This class provide a Persistent Definition for store whether a template will be used for the RSS Items
 */
class opsaRSSExportItemTemplate extends eZPersistentObject
{
	public function __construct( $row ){
		parent::__construct( $row );
	}
	
	public static function definition()
	{
		return array( 'fields' => array( 'ezrss_export_item_id' => array( 'name' => 'ID',
																		  'datatype' => 'integer',
																		  'default' => '',
																		  'required' => true,
																		  'foreign_class' => 'eZRSSExportItem',
																		  'foreign_attribute' => 'id',
																		  'multiplicity', '1..1' ),
										 'use_template_title' => array( 'name' => 'UseTemplateTitle',
																  		'datatype' => 'boolean',
																  		'default' => false,
																  		'required' => true ),
										 'use_template_description' => array( 'name' => 'UseTemplateDescription',
																  			  'datatype' => 'boolean',
																  			  'default' => false,
																  			  'required' => true ) ),
					 'keys' => array( 'ezrss_export_item_id' ),
					 'class_name' => 'opsaRSSExportItemTemplate',
					 'name' => 'opsarss_export_item_template' );
	}
	
	public static function fetch( $id, $as_object = true ){
		return eZPersistentObject::fetchObject( self::definition(), false, array( 'ezrss_export_item_id' => $id ));
	}
}

?>