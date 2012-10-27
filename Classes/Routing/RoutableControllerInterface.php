<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due, Wildside A/S <claus@wildside.dk>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Interface for Controllers that may be routed automatically.
 *
 * Assumes two additional methods on your Controller:
 *
 * - getTypeNum
 *
 * Returning an integer value corresponding to a TS-mapped typeNum
 *
 * - getTypeName
 *
 * Returning a string that is used as the first URI segment and maps to the typeNum
 *
 * Note that only one controller can be executed per typeNum which is
 * slightly different from the expected behavior where an entire
 * plugin can be executed. This small hurdle is related to the fact that
 * realurl can only route to one configuration set per page UID (and in
 * this case the typeNum takes the place of the UID in this logic).
 *
 * In other words:
 *
 * Register one typeNum per controller you wish to have mapped, even
 * if that means you have to insert multiple typeNums pointing to the
 * same Extbase pluginName (one per Controller of this plugin if you need
 * them all to be routable - which can let you imitate a REST approach
 * by using the proper typeName for your controller).
 *
 * @package Fed
 * @subpackage Routing
 */
interface Tx_Fed_Routing_RoutableControllerInterface extends Tx_Extbase_MVC_Controller_ControllerInterface {

}
