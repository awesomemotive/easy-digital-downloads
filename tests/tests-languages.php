<?php


/**
 * @group edd_languages
 */
class Tests_Languages extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_included_languages() {
		// As we work towards getting files included into language packs on WordPress.org, this allows us
		// to make sure we don't keep including translations that hit 100% and shoudl be removed

		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-af.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-an.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ar.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-az.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-be.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-bg_BG.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-bn_BD.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-bs_BA.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ca.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-co.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-cs_CZ.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-cy.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-da_DK.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-de_CH.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-de_DE.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-el.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-eo.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-es_AR.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-es_CL.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-es_ES.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-es_MX.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-es_PE.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-es_VE.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-et.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-eu.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-fa.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-fa_IR.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-fi.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-fo.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-fy.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ga.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-gd.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-gl_ES.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-he_IL.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-hi_IN.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-hr.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-hu_HU.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-id_ID.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-is_IS.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-it_IT.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ja.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-jv.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ka.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ka_GE.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-kk.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-km.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-kn.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ko_KR.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ky.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-lo.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-lt_LT.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-lv.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-mg.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-mk_MK.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-mn.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ms_MY.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-my_MM.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-nb_NO.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ne_NP.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-nl_NL.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-nn_NO.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-oc.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-os.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-pl_PL.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ps.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-pt_BR.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-pt_PT.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ro_RO.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ru_RU.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-sah.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-si_LK.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-sk_SK.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-sl_SI.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-so.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-sq.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-sr_RS.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-su.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-sv_SE.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-sw.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ta_IN.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ta_LK.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-te.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-tg.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-th.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-tl.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-tr_TR.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ug.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-uk.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-ur.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-uz.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-vi.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-zh_CN.mo') );
		$this->assertTrue( file_exists( EDD_PLUGIN_DIR . '/languages/easy-digital-downloads-zh_TW.mo') );

	}

}
