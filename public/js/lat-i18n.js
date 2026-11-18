/**
 * ============================================================================
 * LAT · MOTOR DE INTERNACIONALIZACIÓN TRILINGÜE (ES / QU / EN)
 * ============================================================================
 * Traduce de forma inmediata y persistente toda la interfaz pública y operativa.
 */
(function () {
  const DICT = {
    es: {
      // Navegación y pie
      'nav.brand_sub': 'Gestión Láctea Rural',
      'nav.home': 'Inicio',
      'nav.tools': 'Herramientas',
      'nav.families': 'Empresas Familiares',
      'nav.about': 'Nosotros',
      'nav.panel': 'Mi Panel',
      'nav.panel_full': 'Mi Panel Operativo',
      'nav.login': 'Iniciar Sesión',
      'nav.register': 'Registrarse',
      'nav.logout': 'Cerrar Sesión',
      'nav.lang_title': 'Idioma de la plataforma',
      'nav.menu': 'Menú',
      'footer.desc': 'Software universitario comunitario para microempresas queseras y asociaciones ganaderas de Cusco Provincia. Comisión S/ 0.00 por principio de diseño.',
      'footer.explore': 'Explorar',
      'footer.commitments': 'Compromisos',
      'footer.c1': 'Sin comisión a pequeños productores',
      'footer.c2': 'Dictado por voz y modo una mano',
      'footer.c3': 'Leche ácida a quesillos de 4 días',
      'footer.c4': 'Ley N.° 29733: sin DNI a jornales',

      // Inicio
      'hero.title': 'Donde antes un cuaderno se perdía en el agua, hoy manda la voz.',
      'hero.sub': 'Digitalización accesible para centros de acopio rural, control sanitario de porongo en recepción, monitoreo térmico de tina y liquidaciones transparentes al 0% de comisión.',
      'hero.cta_panel': 'Entrar a Mi Panel Operativo',
      'hero.cta_tools': 'Ver herramientas de terreno',
      'home.tools_title': 'Herramientas de trabajo en campo',
      'home.tools_sub': 'Pensadas para las condiciones reales de las microempresas lácteas: manos con guantes, humedad de suero y conectividad intermitente.',
      'home.families_title': 'Del campo a la quincena, sin cuaderno',
      'home.families_sub': 'Un mismo flujo conecta la ruta de acopio, la planta y la contabilidad.',
      'home.about_title': 'La realidad de nuestras microempresas familiares',
      'home.close_title': 'Lleve el acopio de su planta al panel',
      'home.close_sub': 'Ingrese con su usuario de operario o el correo de la microempresa.',

      // Login y Registro
      'login.title': 'Ingreso a Mi Panel Lácteo',
      'login.sub': 'Acceso seguro · Cusco Provincia. Ingrese con su identificador de operario o el correo de la microempresa.',
      'login.user_label': 'Usuario o correo',
      'login.pass_label': 'Contraseña',
      'login.submit': 'Entrar a Mi Panel',
      'login.no_account': '¿Nueva planta o asociación?',
      'login.go_register': 'Registrar nueva planta aquí',
      'login.profiles_title': 'Perfiles de prueba sembrados',
      'register.title': 'Registro de Planta y Usuario Administrador',
      'register.sub': 'Cree su cuenta de administración junto con su planta quesera. Sin comisiones (S/ 0.00).',
      'register.name': 'Nombre completo del responsable',
      'register.username': 'Usuario de acceso',
      'register.email': 'Correo electrónico (opcional)',
      'register.plant_name': 'Nombre de la planta o quesería',
      'register.plant_loc': 'Ubicación / Provincia',
      'register.price': 'Precio por litro (S/)',
      'register.pass': 'Contraseña (mín. 6 caracteres)',
      'register.pass_confirm': 'Confirmar contraseña',
      'register.submit': 'Crear planta y entrar al Panel',
      'register.have_account': '¿Ya tienes cuenta?',
      'register.go_login': 'Iniciar sesión',

      // Panel Cabecera y KPIs
      'panel.subtitle': 'Centro de acopio y transformación',
      'panel.operator': 'Operador:',
      'panel.role': 'Cargo:',
      'panel.switch_plant': 'Planta activa:',
      'panel.btn_edit_data': 'Editar datos',
      'panel.btn_export_excel': 'Exportar Excel (.xlsx)',
      'panel.btn_download_excel': 'Descargar balance en Excel (.xlsx)',
      'kpi.milk_today': 'Leche recibida hoy',
      'kpi.price_liter': 'Precio vigente por litro',
      'kpi.vat_temp': 'Tina principal',
      'kpi.sanitary': 'Control sanitario 74°',

      // Tabs del Panel
      'tab.acopio': '1. Acopio por voz y tacto',
      'tab.calidad': '2. Calidad sanitaria',
      'tab.tina': '3. Tinas y receta',
      'tab.quincena': '4. Liquidación y ventas',
      'tab.estadisticas': '5. Estadísticas',
      'tab.admin': '6. Plantas y usuarios',

      // Módulo 1: Acopio
      'm1.title': 'Registro diario de recepción',
      'm1.sub': 'Dicte por voz con confirmación hablada o ingrese la cantidad de forma manual.',
      'm1.voice_btn': 'Dictar acopio por voz',
      'm1.producer': 'Ganadero / productor',
      'm1.liters': 'Volumen en litros (L)',
      'm1.touch_pad': 'Botonera táctil rápida (suma litros)',
      'm1.save_btn': 'Guardar entrega en tina',
      'm1.today_list': 'Entregas de hoy',
      'm1.col_time': 'Hora',
      'm1.col_producer': 'Productor',
      'm1.col_liters': 'Litros',
      'm1.col_method': 'Método',
      'm1.col_quality': 'Calidad',
      'm1.col_actions': 'Acciones',

      // Módulo 2: Calidad
      'm2.title': 'Control sanitario en recepción (prueba de alcohol a 74°)',
      'm2.sub': 'Principio de cero desperdicio: la leche con acidez no se desecha ni se multa al productor. Se clasifica de inmediato y se deriva a quesillos frescos de 4 días.',
      'm2.col_status': 'Estado actual',
      'm2.col_dest': 'Destino operativo',
      'm2.col_triage': 'Triage inmediato',
      'm2.approve': 'Aprobar',
      'm2.divert': 'Desviar a quesillo',
      'm2.status_good': 'Óptima',
      'm2.status_acid': 'Con acidez',
      'm2.status_pending': 'Sin probar',
      'm2.dest_main': 'Tina principal (queso / yogur)',
      'm2.dest_quesillo': 'Tina quesillo 4 días',

      // Módulo 3: Múltiples Tinas
      'm3.title': 'Control de múltiples tinas y dosificación',
      'm3.sub': 'Agregue, edite y supervise la curva térmica y receta automática de cada tina de la planta.',
      'm3.add_tina': '+ Agregar nueva tina',
      'm3.edit_tina': 'Editar tina',
      'm3.start_batch': 'Iniciar lote en tina',
      'm3.update_sensor': 'Actualizar sensor',
      'm3.finish_batch': 'Finalizar lote',
      'm3.capacity': 'Capacidad:',
      'm3.no_batch': 'Tina libre (sin lote en proceso)',

      // Módulo 4: Liquidación y Ventas
      'm4.title': 'Liquidación quincenal de proveedores',
      'm4.sub': 'Cálculo neto transparente: bruto menos adelantos en efectivo o queso. Comisión: S/ 0.00.',
      'm4.mov_title': 'Registrar movimiento, venta o ayuda del día',
      'm4.mov_btn': 'Asentar movimiento',
      'm4.recent_title': 'Movimientos y ventas recientes',

      // Módulo 5: Estadísticas
      'm5.title': 'Estadísticas de plantas y ventas',
      'm5.sub': 'Comparativa de desempeño: qué planta vende más, producción de moldes y ranking de proveedores.',
      'm5.who_sells_most': 'Ranking de plantas: ¿Quién vende más?',
      'm5.top_producers': 'Productores con mayor entrega en esta planta',
      'm5.quality_split': 'Calidad sanitaria del período',

      // Módulo 6: Admin Plantas y Usuarios
      'm6.plants_title': 'Administrador de plantas lácteas',
      'm6.add_plant': '+ Nueva planta',
      'm6.users_title': 'Gestión de usuarios y credenciales',
      'm6.add_user': '+ Crear usuario / credenciales',

      // Modales y Confirmaciones JS
      'confirm.title': 'Confirmar cambio',
      'confirm.cancel': 'Cancelar',
      'confirm.accept': 'Sí, confirmar cambio',
      'confirm.quality_msg': '¿Está seguro de que quieres cambiar el estado sanitario de {nombre} ({litros} L) a «{estado}»?',
      'confirm.finish_batch_msg': '¿Está seguro de que quieres finalizar el lote {codigo} y cerrar su registro térmico?',
      'confirm.delete_msg': '¿Está seguro de que quieres eliminar este registro de {detalle}? Esta acción no se puede deshacer.',
      'edit_modal.title': 'Editar datos registrados (Corrección rápida)',
      'edit_modal.sub': 'Corrija aquí entregas, movimientos o ganaderos si ocurrió un error al registrar.',
    },

    qu: {
      'nav.brand_sub': 'Llaqta Lichi Kamay',
      'nav.home': 'Qallariy',
      'nav.tools': 'Llamkanakuna',
      'nav.families': 'Ayllu Ruraykuna',
      'nav.about': 'Ñuqayku',
      'nav.panel': 'Panelniy',
      'nav.panel_full': 'Llamkana Panelniy',
      'nav.login': 'Yaykuy',
      'nav.register': 'Qillqakuy',
      'nav.logout': 'Lluqsiy',
      'nav.lang_title': 'Rimayta akllay',
      'nav.menu': 'Akllana',
      'footer.desc': 'Cusco llaqtapi ayllu kisu ruraqkunapaq yanapakuq llika. Comisión S/ 0.00 mana qullqi mañaq.',
      'footer.explore': 'Maskay',
      'footer.commitments': 'Rimanakuykuna',
      'footer.c1': 'Mana comisión huchuy chakra runapaq',
      'footer.c2': 'Kunkawan hinaspa huk makiwan qillqay',
      'footer.c3': 'Puchqu lichi 4 punchaw kisilluman',
      'footer.c4': 'Ley N.° 29733: mana DNI mañakunchu',

      'hero.title': 'Ñawpaqta qillqana maytu yakuwan chinkaqtin, kunanqa kunkallawanñam.',
      'hero.sub': 'Chakra runakunapaq lichikuna qillqay, allin kaqta qhawariy, tina ruphay tupuy, chanta qusqa chaninpi qunakuy (0% comisión).',
      'hero.cta_panel': 'Llamkana Panelniyman Yaykuy',
      'hero.cta_tools': 'Chakra llamkanakunata qhaway',
      'home.tools_title': 'Chakrapi llamkana yanapakuykuna',
      'home.tools_sub': 'Chiri pampapi, guantesniyuq makiwanpas allinta llamkananpaq rurasqa.',
      'home.families_title': 'Chakramanta quincena qullqikama, mana maytuwan',
      'home.families_sub': 'Huk llikallapi lichi huñuyta, plantata hinaspa yupaykunata tinkuchin.',
      'home.about_title': 'Ayllu kisu ruraqkunapa kawsaynin',
      'home.close_title': 'Plantaykipa llamkayninta panelman apamuy',
      'home.close_sub': 'Llamkaq sutiykiwan utaq correo nisqawan yaykuy.',

      'login.title': 'Lichi Panelman Yaykuy',
      'login.sub': 'Allin waqaychasqa yaykuna · Cusco Provincia. Llamkaq sutiykita qillqay.',
      'login.user_label': 'Usuario utaq correo',
      'login.pass_label': 'Pakasqa simi',
      'login.submit': 'Panelniyman Yaykuy',
      'login.no_account': '¿Musuq plantachu kanki?',
      'login.go_register': 'Kaypi musuq plantata qillqay',
      'login.profiles_title': 'Yachachinapaq churakusqa sutikuna',
      'register.title': 'Musuq Planta hinaspa Kamachiq Qillqakuy',
      'register.sub': 'Kisu rurana plantaykita qillqay. Mana comisión (S/ 0.00).',
      'register.name': 'Kamachiqpa hunt’asqa sutin',
      'register.username': 'Yaykunapaq usuario',
      'register.email': 'Correo electrónico (munaspaqa)',
      'register.plant_name': 'Plantapa sutin',
      'register.plant_loc': 'Maypi kasqan / Provincia',
      'register.price': 'Sapa litropa chanin (S/)',
      'register.pass': 'Pakasqa simi (6 aswan)',
      'register.pass_confirm': 'Pakasqa simita kutichiy',
      'register.submit': 'Plantata kamariy hinaspa yaykuy',
      'register.have_account': '¿Ñachu cuentayki kan?',
      'register.go_login': 'Yaykuy',

      'panel.subtitle': 'Lichi huñuna hinaspa rurana wasi',
      'panel.operator': 'Llamkaq:',
      'panel.role': 'Llamkaynin:',
      'panel.switch_plant': 'Llamkaq planta:',
      'panel.btn_edit_data': 'Datoskunata allichay',
      'panel.btn_export_excel': 'Excel (.xlsx) hurquy',
      'panel.btn_download_excel': 'Excel (.xlsx) yupayta uraykachiy',
      'kpi.milk_today': 'Kunan chaskisqa lichi',
      'kpi.price_liter': 'Sapa litropa chanin',
      'kpi.vat_temp': 'Hatun tina ruphay',
      'kpi.sanitary': '74° qhali kay qhaway',

      'tab.acopio': '1. Kunkawan lichi huñuy',
      'tab.calidad': '2. Qhali kay (74°)',
      'tab.tina': '3. Tinakuna hinaspa receta',
      'tab.quincena': '4. Quincena hinaspa rantikuy',
      'tab.estadisticas': '5. Yupaykuna (Estadística)',
      'tab.admin': '6. Plantakuna hinaspa llamkaqkuna',

      'm1.title': 'Sapa punchaw lichi chaskiy',
      'm1.sub': 'Kunkawan rimaspa utaq makiwan litrokunata qillqay.',
      'm1.voice_btn': 'Kunkawan lichi qillqay',
      'm1.producer': 'Lichi quq runa',
      'm1.liters': 'Litrokuna (L)',
      'm1.touch_pad': 'Utqaylla yapana ñit’ina',
      'm1.save_btn': 'Tinaman waqaychay',
      'm1.today_list': 'Kunan punchaw chaskiykuna',
      'm1.col_time': 'Pacha',
      'm1.col_producer': 'Lichi quq',
      'm1.col_liters': 'Litrokuna',
      'm1.col_method': 'Imayna',
      'm1.col_quality': 'Allin kay',
      'm1.col_actions': 'Ruraykuna',

      'm2.title': 'Chaskispa qhali kay qhaway (74° alcoholwan)',
      'm2.sub': 'Mana ima lichi chinkachiy: puchqu lichiqa mana wikch’ukunchu, 4 punchaw kisillumanmi rin.',
      'm2.col_status': 'Kunan kaynin',
      'm2.col_dest': 'Mayman rinan',
      'm2.col_triage': 'Utqaylla akllay',
      'm2.approve': 'Allinmi',
      'm2.divert': 'Kisilluman kachay',
      'm2.status_good': 'Sumaq allin',
      'm2.status_acid': 'Puchquyuq',
      'm2.status_pending': 'Manaraq qhawasqa',
      'm2.dest_main': 'Hatun tina (kisu / yogur)',
      'm2.dest_quesillo': '4 punchaw kisillo tina',

      'm3.title': 'Achka tinakuna kamay hinaspa receta',
      'm3.sub': 'Musuq tinakunata yapay, allichay hinaspa sapa tinapa ruphayninta qhaway.',
      'm3.add_tina': '+ Musuq tinata yapay',
      'm3.edit_tina': 'Tinata allichay',
      'm3.start_batch': 'Tinapi lote qallariy',
      'm3.update_sensor': 'Ruphayta musuqyachiy',
      'm3.finish_batch': 'Loteta tukuy',
      'm3.capacity': 'Hayka lichi:',
      'm3.no_batch': 'Ch’usaq tina (mana lote kanchu)',

      'm4.title': 'Quincena qullqi yupay',
      'm4.sub': 'Chuya yupay: ñawpaq mañakuykunata qichuspa. Comisión: S/ 0.00.',
      'm4.mov_title': 'Qullqi kuyuy, rantikuy utaq punchaw yanapay qillqay',
      'm4.mov_btn': 'Qillqayta waqaychay',
      'm4.recent_title': 'Chayraq qullqi kuyuykuna hinaspa rantikuykuna',

      'm5.title': 'Plantakunapa yupaynin hinaspa rantikuy',
      'm5.sub': '¿Mayqin plantataq aswanta rantikun? Yupaykuna hinaspa lichi quqkuna.',
      'm5.who_sells_most': 'Plantakuna atipanakuy: ¿Pitaq aswanta rantikun?',
      'm5.top_producers': 'Aswan lichi quq runakuna',
      'm5.quality_split': 'Lichipa qhali kaynin',

      'm6.plants_title': 'Lichi plantakuna kamachiq',
      'm6.add_plant': '+ Musuq planta',
      'm6.users_title': 'Llamkaqkuna hinaspa yaykuna kamay',
      'm6.add_user': '+ Musuq llamkaq kamay',

      'confirm.title': 'Tikrayta chiqaqchay',
      'confirm.cancel': 'Ama tikraychu',
      'confirm.accept': 'Arí, tikrayta munani',
      'confirm.quality_msg': '¿Chiqapchachu {nombre} ({litros} L) lichipa kayninta «{estado}» nisqaman tikrayta munanki?',
      'confirm.finish_batch_msg': '¿Chiqapchachu {codigo} loteta tukuyta munanki?',
      'confirm.delete_msg': '¿Chiqapchachu kay {detalle} qillqasqata pichayta munanki?',
      'edit_modal.title': 'Qillqasqa datoskunata allichay (pantay kaptin)',
      'edit_modal.sub': 'Pantay kaptinqa lichi litrokunata, qullqi kuyuykunata utaq lichi quqkunata kaypi allichay.',
    },

    en: {
      'nav.brand_sub': 'Rural Dairy Management',
      'nav.home': 'Home',
      'nav.tools': 'Tools',
      'nav.families': 'Family Dairies',
      'nav.about': 'About Us',
      'nav.panel': 'My Dashboard',
      'nav.panel_full': 'My Operations Panel',
      'nav.login': 'Sign In',
      'nav.register': 'Register',
      'nav.logout': 'Sign Out',
      'nav.lang_title': 'Platform Language',
      'nav.menu': 'Menu',
      'footer.desc': 'Community university software for family cheese dairies and livestock associations in Cusco Province. S/ 0.00 commission by design.',
      'footer.explore': 'Explore',
      'footer.commitments': 'Commitments',
      'footer.c1': 'Zero commission for small farmers',
      'footer.c2': 'Voice dictation & one-handed mode',
      'footer.c3': 'Acidic milk routed to 4-day quesillo',
      'footer.c4': 'Law 29733: no ID required for day help',

      'hero.title': 'Where notebooks once drowned in whey, today your voice leads.',
      'hero.sub': 'Accessible digital intake for rural dairy plants, rapid milk quality inspection, vat thermal monitoring, and fair bi-weekly settlements at 0% commission.',
      'hero.cta_panel': 'Open My Operations Panel',
      'hero.cta_tools': 'View field tools',
      'home.tools_title': 'Field-ready work tools',
      'home.tools_sub': 'Built for real dairy plant conditions: gloved hands, whey humidity, and intermittent connectivity.',
      'home.families_title': 'From pasture to bi-weekly payout, paperless',
      'home.families_sub': 'One seamless workflow connects milk routes, plant processing, and accounting.',
      'home.about_title': 'The reality of our family dairy businesses',
      'home.close_title': 'Bring your plant intake to the dashboard',
      'home.close_sub': 'Sign in with your operator username or dairy email.',

      'login.title': 'Sign In to Dairy Panel',
      'login.sub': 'Secure access · Cusco Province. Enter your operator username or plant email.',
      'login.user_label': 'Username or email',
      'login.pass_label': 'Password',
      'login.submit': 'Enter My Panel',
      'login.no_account': 'New dairy plant or association?',
      'login.go_register': 'Register a new plant here',
      'login.profiles_title': 'Seeded demo profiles',
      'register.title': 'Register New Plant & Admin Account',
      'register.sub': 'Create your admin credentials along with your dairy plant. Zero commission (S/ 0.00).',
      'register.name': 'Full name of administrator',
      'register.username': 'Username',
      'register.email': 'Email address (optional)',
      'register.plant_name': 'Dairy plant name',
      'register.plant_loc': 'Location / Province',
      'register.price': 'Price per liter (S/)',
      'register.pass': 'Password (min 6 chars)',
      'register.pass_confirm': 'Confirm password',
      'register.submit': 'Create plant & open Panel',
      'register.have_account': 'Already have an account?',
      'register.go_login': 'Sign in',

      'panel.subtitle': 'Milk collection & processing center',
      'panel.operator': 'Operator:',
      'panel.role': 'Role:',
      'panel.switch_plant': 'Active plant:',
      'panel.btn_edit_data': 'Edit data',
      'panel.btn_export_excel': 'Export Excel (.xlsx)',
      'panel.btn_download_excel': 'Download Excel Report (.xlsx)',
      'kpi.milk_today': 'Milk received today',
      'kpi.price_liter': 'Current price per liter',
      'kpi.vat_temp': 'Main pasteurizing vat',
      'kpi.sanitary': '74° sanitary check',

      'tab.acopio': '1. Voice & touch intake',
      'tab.calidad': '2. Sanitary quality',
      'tab.tina': '3. Vats & recipe',
      'tab.quincena': '4. Payouts & sales',
      'tab.estadisticas': '5. Statistics',
      'tab.admin': '6. Plants & users',

      'm1.title': 'Daily milk intake log',
      'm1.sub': 'Dictate by voice with spoken confirmation or enter liters manually.',
      'm1.voice_btn': 'Dictate intake by voice',
      'm1.producer': 'Dairy farmer / producer',
      'm1.liters': 'Volume in liters (L)',
      'm1.touch_pad': 'Quick touch keypad (adds liters)',
      'm1.save_btn': 'Save delivery to vat',
      'm1.today_list': "Today's deliveries",
      'm1.col_time': 'Time',
      'm1.col_producer': 'Producer',
      'm1.col_liters': 'Liters',
      'm1.col_method': 'Method',
      'm1.col_quality': 'Quality',
      'm1.col_actions': 'Actions',

      'm2.title': 'Intake sanitary inspection (74° alcohol test)',
      'm2.sub': 'Zero-waste principle: acidic milk is never discarded or fined. It is routed immediately to fresh 4-day quesillo.',
      'm2.col_status': 'Current status',
      'm2.col_dest': 'Operational route',
      'm2.col_triage': 'Immediate triage',
      'm2.approve': 'Approve',
      'm2.divert': 'Route to quesillo',
      'm2.status_good': 'Optimal',
      'm2.status_acid': 'Acidic',
      'm2.status_pending': 'Untested',
      'm2.dest_main': 'Main vat (cheese / yogurt)',
      'm2.dest_quesillo': '4-day quesillo vat',

      'm3.title': 'Multi-vat control & recipe dosing',
      'm3.sub': 'Add, edit and monitor thermal curves and automated recipes across all plant vats.',
      'm3.add_tina': '+ Add new vat',
      'm3.edit_tina': 'Edit vat',
      'm3.start_batch': 'Start batch in vat',
      'm3.update_sensor': 'Update sensor',
      'm3.finish_batch': 'Finish batch',
      'm3.capacity': 'Capacity:',
      'm3.no_batch': 'Idle vat (no active batch)',

      'm4.title': 'Bi-weekly producer settlement',
      'm4.sub': 'Transparent net calculation: gross minus cash or cheese advances. Commission: S/ 0.00.',
      'm4.mov_title': 'Record movement, product sale or day help',
      'm4.mov_btn': 'Post entry',
      'm4.recent_title': 'Recent movements & sales',

      'm5.title': 'Plant statistics & sales ranking',
      'm5.sub': 'Performance comparison: which plant sells the most, mold output and top producers.',
      'm5.who_sells_most': 'Plant ranking: Who sells the most?',
      'm5.top_producers': 'Top milk producers in this plant',
      'm5.quality_split': 'Period sanitary quality breakdown',

      'm6.plants_title': 'Dairy plants manager',
      'm6.add_plant': '+ New plant',
      'm6.users_title': 'User & credential management',
      'm6.add_user': '+ Create user credentials',

      'confirm.title': 'Confirm change',
      'confirm.cancel': 'Cancel',
      'confirm.accept': 'Yes, confirm change',
      'confirm.quality_msg': 'Are you sure you want to change the sanitary status of {nombre} ({litros} L) to "{estado}"?',
      'confirm.finish_batch_msg': 'Are you sure you want to finish batch {codigo} and close its thermal log?',
      'confirm.delete_msg': 'Are you sure you want to delete this record ({detalle})? This action cannot be undone.',
      'edit_modal.title': 'Edit recorded data (Quick correction)',
      'edit_modal.sub': 'Fix milk deliveries, financial movements or producer records if a mistake was made.',
    },
  };

  function getSavedLang() {
    try {
      const saved = localStorage.getItem('lat_lang');
      if (saved && DICT[saved]) return saved;
    } catch (_) {}
    const htmlLang = (document.documentElement.lang || 'es').slice(0, 2).toLowerCase();
    return DICT[htmlLang] ? htmlLang : 'es';
  }

  function t(key, vars) {
    const lang = getSavedLang();
    let str = (DICT[lang] && DICT[lang][key]) || DICT.es[key] || key;
    if (vars) {
      Object.keys(vars).forEach((k) => {
        str = str.replace(new RegExp('\\{' + k + '\\}', 'g'), vars[k]);
      });
    }
    return str;
  }

  function applyTranslations(lang) {
    const valid = DICT[lang] ? lang : 'es';
    const map = DICT[valid];

    document.documentElement.lang = valid;
    try {
      localStorage.setItem('lat_lang', valid);
    } catch (_) {}

    document.querySelectorAll('[data-i18n]').forEach((el) => {
      const key = el.getAttribute('data-i18n');
      if (map[key]) {
        el.textContent = map[key];
      }
    });

    document.querySelectorAll('[data-i18n-placeholder]').forEach((el) => {
      const key = el.getAttribute('data-i18n-placeholder');
      if (map[key]) {
        el.setAttribute('placeholder', map[key]);
      }
    });

    const labelDesktop = document.getElementById('lang-current-label');
    if (labelDesktop) labelDesktop.textContent = valid.toUpperCase();

    document.querySelectorAll('.lang-mobile-current').forEach((el) => {
      el.textContent = valid.toUpperCase();
    });

    document.querySelectorAll('.lang-opt').forEach((opt) => {
      const activo = opt.dataset.lang === valid;
      opt.classList.toggle('activo', activo);
      opt.setAttribute('aria-selected', activo ? 'true' : 'false');
    });

    document.querySelectorAll('.btn-lang-choice').forEach((btn) => {
      const activo = btn.dataset.lang === valid;
      btn.classList.toggle('activo', activo);
      btn.setAttribute('aria-pressed', activo ? 'true' : 'false');
    });
  }

  window.LAT_I18N = {
    dict: DICT,
    t: t,
    getLang: getSavedLang,
    apply: applyTranslations,
  };
})();
