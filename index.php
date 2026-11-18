<?php
/**
 * Redirección automática de la raíz hacia el directorio public/
 * Diseñado para despliegue directo en Apache / XAMPP
 */
header('Location: public/', true, 301);
exit;
