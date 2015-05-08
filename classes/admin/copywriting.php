<?php

/**

  The Initial Developer of the Original Code is
  Matthieu  - http://www.programmation-facile.com/

  Contributor(s) :

 */


/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * Admin class
 * Connect app AWeber Dev Facile
 * 
 */
class AWeberDevFacileCopy_Admin
{

	public function __construct(){}



	/**
	 * Add a form for subscribe copywriting facile
	 * 
	 * @return [type] [description]
	 */
	public function addFormCopywritingFacile()
    {
		echo '<h2>'.__( 'Title-AWeber-Dev-Facile-Marketing', DEV_NAME ).'</h2>
            <p>'.__( 'Text-AWeber-Dev-Facile-Marketing', DEV_NAME ).'</p>';

        // form AWeber
        if( get_locale() == 'fr_FR') 
        	echo '<p align="left"><div class="AW-Form-1317839450"></div>
			<script type="text/javascript">(function(d, s, id) {
			    var js, fjs = d.getElementsByTagName(s)[0];
			    if (d.getElementById(id)) return;
			    js = d.createElement(s); js.id = id;
			    js.src = "//forms.aweber.com/form/50/1317839450.js";
			    fjs.parentNode.insertBefore(js, fjs);
			    }(document, "script", "aweber-wjs-q11oy7rnb"));
			</script></p>';

		else
			echo '<p align="left"><div class="AW-Form-771647267"></div>
			<script type="text/javascript">(function(d, s, id) {
			    var js, fjs = d.getElementsByTagName(s)[0];
			    if (d.getElementById(id)) return;
			    js = d.createElement(s); js.id = id;
			    js.src = "//forms.aweber.com/form/67/771647267.js";
			    fjs.parentNode.insertBefore(js, fjs);
			    }(document, "script", "aweber-wjs-rkj4oivt8"));
			</script></p>';

	}// end if connectToAWeberAccount 

}


