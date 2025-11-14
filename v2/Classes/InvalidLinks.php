<?php
	namespace v2\Classes;

	use v2\Database\Entity\InvalidLink;
	use v2\Manager;
	use v2\Traits\TextHandler;

	class InvalidLinks
	{
		use TextHandler;

		public function __construct()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'InvalidLinks.html', 'r');
			$this->content = fread($file, 10000);
			$this->cssFiles = [
				'Home',
				'InvalidLinks',
			];

			$this->jsFiles = [
				'InvalidLinks'
			];
		}

		public function buildContent()
		{
			$this->placeHolders = [
				'table_rows' => $this->generateTableRowsHtml(),
			];
			$this->fillPlaceHolders();
		}

		private function generateTableRowsHtml()
		{
			$groupedData = $this->getGroupedInvalidLinks();
			$html = '';
			$row = 0;

			foreach ($groupedData as $entryId => $data) {
				$rowColorClass = 'row-color-' . ($row % 2);

				// Parent Row
				$html .= sprintf(
					'<tr class="parent-row %s" data-entry-id="%d">',
					$rowColorClass,
					$entryId
				);
				$html .= sprintf('<td><a href="?v=2&id=%d" target="_blank">%d</a></td>', $entryId, $entryId);
				$html .= sprintf('<td>%d invalid link(s) found. Click to expand.</td>', count($data['links']));
				$html .= sprintf('<td>%s %d IP(s)</td>', ($data['total_unique_ips'] ? '▼' : ''), $data['total_unique_ips']);
				$html .= '</tr>';

				// Child Rows
				foreach ($data['links'] as $index => $link) {
					$ipListId = "ips-list-{$entryId}-{$index}";
					$html .= sprintf(
						'<tr class="child-row child-of-%d %s" style="display: none;">',
						$entryId,
						$rowColorClass
					);
					$html .= '<td></td>'; // Empty cell for Entry ID
					$html .= sprintf('<td><a href="%s" target="_blank">%s</a></td>', $link['url'], $link['url']);
					
					// Clickable IP Count Cell
					$html .= '<td>';
					$html .= sprintf('<div id="%s" class="ip-list">%s</div>', $ipListId, $link['ip_string']);
					$html .= '</td>';
					
					$html .= '</tr>';
				}
				$row++;
			}
			return $html;
		}
		
		private function getGroupedInvalidLinks()
		{
			$invalidLinkRepository = app('em')->getRepository(InvalidLink::class);
			$downloadRepository = app('em')->getRepository(\v2\Database\Entity\Download::class);
			$items = $invalidLinkRepository->findAll([], ['id' => 'DESC']);

			$groupedLinks = [];
			foreach ($items as $item) {
				$link = $item->getLink();
				if (!$link) continue;

				$entryId = $item->getEntry(true);
				$ips = $downloadRepository->findIpsByLinkInLastDays($link->getId(), 30);
				$uniqueIps = array_unique($ips);

				if (!isset($groupedLinks[$entryId])) {
					$groupedLinks[$entryId] = ['links' => [], 'all_ips' => []];
				}

				$groupedLinks[$entryId]['links'][] = [
					'url' => $link->getUrl(),
					'ip_count' => count($uniqueIps),
					'ip_string' => implode('<br>', $uniqueIps),
				];
				$groupedLinks[$entryId]['all_ips'] = array_merge($groupedLinks[$entryId]['all_ips'], $uniqueIps);
			}

			// Calculate total unique IPs per entry
			foreach ($groupedLinks as $entryId => &$data) {
				$data['total_unique_ips'] = count(array_unique($data['all_ips']));
				unset($data['all_ips']); // Clean up temporary array
			}

			return $groupedLinks;
		}
	}