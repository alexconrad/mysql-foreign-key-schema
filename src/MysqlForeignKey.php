<?php


namespace MysqlForeignKeySchema;


use EasyMysql\DataProvider;

class MysqlForeignKey
{
    /** @var DataProvider */
    private $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function go()
    {
        $ret = [];
        $tables = $this->dataProvider->column('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?', 'TABLE_NAME', ['marketplace_local']);
        foreach ($tables as $table) {
            $columns = $this->dataProvider->column('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ', 'COLUMN_NAME', [
                'marketplace_local',
                $table
            ]);


            foreach ($columns as $column) {
                $refs = $this->dataProvider->all("SELECT
  TABLE_NAME,COLUMN_NAME
FROM
  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_SCHEMA = ? AND
  REFERENCED_TABLE_NAME = ? AND
  REFERENCED_COLUMN_NAME = ?;", ['marketplace_local', $table, $column]);

                if (!empty($refs)) {
                    foreach ($refs as $ref) {
                        $ret[$table][$column][] = [$ref['TABLE_NAME'], $ref['COLUMN_NAME']];
                    }
                }
            }

        }


        $diagramContent = $this->graphVizDiagram('marketplace', $ret);

        $filename = tempnam(sys_get_temp_dir(),'graphvix');
        $handle = fopen($filename, 'w');
        fwrite($handle, $diagramContent);
        fclose($handle);

        // install http://www.graphviz.org/doc/info/command.html
        passthru('dot -Tbmp '.$filename.' -ofk.bmp');
    }


    private function graphVizDiagram($diagramName, $refs)
    {
        $ret = 'digraph '.$diagramName.' {
  { 
    node [margin=0 fontcolor=blue fontsize=12 width=0.5 shape=box style=filled]'."\n";

        $boxesWith = [];
        foreach ($refs as $tableName=>$references) {
            $boxesWith[$tableName] = 1;
        }

        $boxesWithout = [];
        foreach ($refs as $tableName=>$references) {
            foreach ($references as $referencedColumn=>$referencedLocations) {
                foreach ($referencedLocations as $referencedLocation) {
                    if (array_key_exists($referencedLocation[0], $boxesWith) === false) {
                        $boxesWithout[$referencedLocation[0]] = 1;
                    }
                }
            }
        }

        foreach (array_keys($boxesWith) as $box) {
            $ret .= "    $box [fillcolor=yellow  margin=\"0.11,0.055\"]\n";
        }
        foreach (array_keys($boxesWithout) as $box) {
            $ret .= "    $box [fillcolor=white label=\"".str_replace('_', "\\n", $box)."\"]\n";
        }
        $ret .= "  }\n";

        foreach (array_keys($boxesWith) as $box) {
            $ret .= "  $box -> {";

            $rn = [];
            foreach ($refs[$box] as $column=>$referencedLocations) {
                foreach ($referencedLocations as $referencedLocation) {
                    $rn[] = $referencedLocation[0];
                }
            }

            $ret .= implode(' ', $rn);
            $ret .= "}\n";
        }
        $ret .= '}';

        return $ret;


    }

    private function qwe() {
echo '
digraph G {
  { 
    node [margin=0 fontcolor=blue fontsize=12 width=0.5 shape=box style=filled]
    b [fillcolor=yellow label="master account payout channels" margin="0.11,0.055"]
    d [fixedsize=shape label="label d"]
  }
  a -> {c d}
  b -> {c d}
  c -> {b}
  e -> {a}
}
';
    }

}
