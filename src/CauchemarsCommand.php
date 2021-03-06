<?php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CauchemarsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate-compilation')
            ->setDescription('Generates a music compilation')
            ->addOption('count', null, InputOption::VALUE_OPTIONAL, 'Number of compilations to generate', 1)
            ->addOption('prefix', null, InputOption::VALUE_OPTIONAL, 'Compilations prefix', 'ananas')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
	// Parse cli arguments
	$count = $input->getOption('count');
	// Setup dependencies
	require(__DIR__.'/Mp3FileObject.class.php');

	// create a log channel
	$logStderr = new \Monolog\Logger('stderr');
	$logStderr->pushHandler(new \Monolog\Handler\StreamHandler('php://stderr'));

	// Constants
	define('LENGTH_CD', 74 * 60);

	// Setup output stream
	$stdout = new SplFileObject('php://stdout');
	$num = 1;
	while ($num <= $count) {
		// Query data.musiques-incongrues.net for MP3 links
		$urlQuery = 'http://data.musiques-incongrues.net/collections/links/segments/mp3/get?sort_field=random&limit=50&format=json';
		$curl = curl_init($urlQuery);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = json_decode(curl_exec($curl), true);
		unset($response['num_found']);

		$logStderr->addInfo(sprintf('Generating compilation %d/%d', $num, $count));
		// Generate compilation ID
		$id = uniqid($input->getOption('prefix').'_');
		$logStderr->addInfo(sprintf('Compilation ID : %s', $id));
		$dirCompilation = __DIR__.'/../var/compilations/'.$id;
		mkdir($dirCompilation);
		$lengthCompilation = 0;
		$tracks = array();
		foreach ($response as $resource) {
			$manifest = array();
			$logStderr->addInfo(sprintf('Downloading %s', $resource['url']));
			$curl = curl_init($resource['url']);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$track = curl_exec($curl);
			$responseInfo = curl_getinfo($curl);
			if ($responseInfo['http_code'] <= 400 && strlen($track) > 0) {
				$mp3FilePath = sprintf('%s/%s', $dirCompilation, urldecode(basename($resource['url'])));
				$logStderr->addInfo(sprintf('Saving file to %s', $mp3FilePath));
				file_put_contents($mp3FilePath, $track);
				$fileMp3 = new Mp3FileObject($mp3FilePath);
				$metadata = $fileMp3->get_metadata();
				if (!isset($metadata['Bitrate'])) {
					continue;
				}
				$lengthTrack = Mp3FileObject::getduration($metadata, $fileMp3->tell2());
				if ($lengthTrack == 'unknown') {
					continue;
				}
				$logStderr->addInfo(sprintf('Track length : %s', $lengthTrack));
				$lengthCompilation += $lengthTrack;
				$logStderr->addInfo(sprintf('Compilation length : %s / %s', $lengthCompilation, LENGTH_CD));
				$tracks[] = $resource;
				if ($lengthCompilation > LENGTH_CD) {
					$logStderr->addInfo('Reached CD length, aborting');
					unlink($mp3FilePath);
					unset($tracks[count($tracks) - 1]);
					break;
				}
			} else {
				$logStderr->addWarning(sprintf('Could not download file %s [%s]', $resource['url'], $responseInfo['http_code']));
			}
		}

		// Build manifest
		$manifest = array();
		$manifest[] = "Les Cauchemars de l'Amateur de Fondue à l'Ananas";
		$manifest[] = "================================================";
		$manifest[] = '';
		$manifest[] = 'Compilation ID : ' . $id;
		$manifest[] = 'Date de génération : ' . date('r');
		$manifest[] = '';
		$manifest[] = 'Quid ?';
		$manifest[] = '======';
		$manifest[] = '';
		$manifest[] = 'Depuis la création du forum des Musiques Incongrues (http://www.musiques-incongrues.net)';
		$manifest[] = 'en 2006 nous constituons';
		$manifest[] = 'une base de données (http://data.musiques-incongrues.net) regroupant tous les liens postés';
		$manifest[] = 'par nos contributeurs.';
		$manifest[] = '';
		$manifest[] = 'Ce corpus regroupe plus de 50 000 liens à ce jour.';
		$manifest[] = '';
		$manifest[] = 'Chacune de ces compilations est composée d’une sélection aléatoire'; 
		$manifest[] = 'de morceaux issus de cette base de données.';
		$manifest[] = '';
		$manifest[] = 'Playlist :';
		$manifest[] = '==========';
		$i = 1;
		foreach ($tracks as $resource) {
			$manifest[] = sprintf('    %d. %s (in %s)', $i++, urldecode(basename($resource['url'])), utf8_encode($resource['discussion_name']));
		}
		$manifest[] = '';
		$manifestTxt = implode("\n", $manifest);
		file_put_contents($dirCompilation.'/manifest.txt', $manifestTxt);
		$logStderr->addInfo("\n".$manifestTxt);
		$num++;
	}
    }
}

