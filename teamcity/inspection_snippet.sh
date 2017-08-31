project="/opt/project"
profile="$project/.idea/inspectionProfiles/Project_Default.xml"
output="$project/output"
subdir="$project/src"
mkdir -p $output
rm $output/*

#todo: deploy license and .idea configuration folder

#run phpstorm
/opt/PhpStorm-172.3317.83/bin/inspect.sh $project $profile $output -d $subdir

#process output into teamcity understandable format
php $project/src/console.php teamcity:TransformCodeAnalysisOutput $output $output/compiled.pmd