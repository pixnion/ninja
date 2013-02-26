<?php

class ListView_Controller extends Authenticated_Controller {
	public function index($q = "[hosts] all") {
		$this->xtra_js = array();
		$query = $this->input->get('q', $q);
		$query_order = $this->input->get('s', '');
		
		
		$basepath = 'modules/lsfilter/';

		$this->xtra_js[] = 'index.php/manifest/js/orm_structure.js';
		
		$this->xtra_js[] = $basepath.'js/LSFilter.js';
		$this->xtra_js[] = $basepath.'js/LSFilterLexer.js';
		$this->xtra_js[] = $basepath.'js/LSFilterParser.js';
		$this->xtra_js[] = $basepath.'js/LSFilterPreprocessor.js';
		$this->xtra_js[] = $basepath.'js/LSFilterVisitor.js';

		$this->xtra_js[] = $basepath.'js/LSColumns.js';
		$this->xtra_js[] = $basepath.'js/LSColumnsLexer.js';
		$this->xtra_js[] = $basepath.'js/LSColumnsParser.js';
		$this->xtra_js[] = $basepath.'js/LSColumnsPreprocessor.js';
		$this->xtra_js[] = $basepath.'js/LSColumnsVisitor.js';
		
		$this->xtra_js[] = $basepath.'media/js/lib.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterVisitors.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterRenderer.js';
		$this->xtra_js[] = 'index.php/listview/renderer/table.js';
		
		$this->xtra_js[] = $basepath.'media/js/LSFilterMain.js';

		$this->xtra_js[] = $basepath.'media/js/LSFilterHistory.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterList.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterListTableDesc.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterSaved.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterTextarea.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterVisual.js';
		
		$this->xtra_js[] = $basepath.'media/js/LSFilterMultiselect.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterInputWindow.js';
		
		$this->xtra_js[] = 'index.php/listview/columns_config/vars';

		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css = array();
		$this->xtra_css[] = $basepath.'views/css/LSFilterStyle.css';
		$this->template->css_header->css = $this->xtra_css;

		$this->template->title = _('List view');
		$this->template->content = $lview = $this->add_view('listview/listview');
		$this->template->disable_refresh = true;

		$lview->query = $query;
		$lview->query_order = $query_order;
	}
	
	public function columns_config($tmp = false) {
		
		/* Fetch all column configs for user */
		$columns = array();
		foreach( Kohana::config('listview.columns') as $table => $default ) {
			$columns[$table] = config::get('listview.columns.'.$table, '*');
		}
		
		/* This shouldn't have a standard template */
		$this->template = $lview = $this->add_view('listview/js');
		$this->template->vars = array(
			'lsfilter_list_columns' => $columns
			);
		
		/* Render and die... cant print anything like profiler output here */
		$this->template->render(true);
		exit();
	}
	
	public function fetch_ajax() {
		$query = $this->input->get('query','');
		$columns = $this->input->get('columns',false);
		$sort = $this->input->get('sort',array());
		
		$limit = $this->input->get('limit',false);
		$offset = $this->input->get('offset',false);

		if( $limit === false ) {
			return json::ok( array( 'status' => 'error', 'data' => "No limit specified") );
		}
		
		/* TODO: Fix sorting better sometime
		 * Do it though ORM more orm-ly
		 * Check if columns exists and so on...
		 */
		$sort = array_map(function($el){return str_replace('.','_',$el);},$sort);
		
		try {
			$result_set = ObjectPool_Model::get_by_query( $query );
			
			$data = array();
			foreach( $result_set->it($columns,$sort,$limit,$offset) as $elem ) {
				$data[] = $elem->export();
			}

			return json::ok( array(
				'status' => 'success',
				'totals' => $result_set->get_totals(),
				'data' => $data,
				'table' => $result_set->get_table(),
				'count' => count($result_set)
			) );
		} catch( LSFilterException $e ) {
			return json::ok( array(
				'status' => 'error',
				'data' => $e->getMessage().' at "'.substr($e->get_query(), $e->get_position()).'"',
				'query' => $e->get_query(),
				'position' => $e->get_position()
				));
		} catch( Exception $e ) {
			$this->log->log('error', $e->getMessage() . ' at ' . $e->getFile() . '@' . $e->getLine());
			
			return json::ok( array(
				'status' => 'error',
				'data' => $e->getMessage().' at '.$e->getFile().'@'.$e->getLine()
				));
		}
	}

	public function fetch_saved_queries() {
		$queries = LSFilter_Saved_Queries_Model::get_queries();
		return json::ok( array( 'status' => 'success', 'data' => $queries ) );
	}

	public function save_query() {
		$name = $this->input->get('name',false);
		$query = $this->input->get('query','');
		$scope = $this->input->get('scope','user');

		try {
			
			$result = LSFilter_Saved_Queries_Model::save_query($name, $query, $scope);
			
			if( $result !== false )
				return json::ok( array('status'=>'error', 'data' => $result) );
			
			
			return json::ok( array( 'status' => 'success', 'data' => 'success' ) );
		}
		catch( Exception $e ) {
			return json::ok( array( 'status' => 'error', 'data' => $e->getMessage() ) );
		}
	}


	/**
	 * Return a manifest variable as a javascript file, for loading through a script tag
	 */
	public function renderer( $name = false ) {
		if( substr( $name, -3 ) == '.js' ) {
			$name = substr( $name, 0, -3 );
		}
		
		$this->auto_render = false;
		$renderers_files = Module_Manifest_Model::get( 'lsfilter_renderers' );
	
		header('Content-Type: text/javascript');
		
		print "var listview_renderer_".$name." = {};\n\n";
		
		$files = array();
		if( isset( $renderers_files[$name] ) ) {
			$files = $renderers_files[$name];
		}
		
		foreach( $files as $renderer ) {
			print "\n/".str_repeat('*',79)."\n";
			print " * Output file: ".$renderer."\n";
			print " ".str_repeat('*',78)."/\n";
			if( is_readable(DOCROOT.$renderer) ) {
				readfile(DOCROOT.$renderer);
			} else {
				print "// ERROR: Can't open file...\n\n";
			}
		}
	}
}