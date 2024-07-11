                "defTaulaDadesBlocs": {
                    "type": "object",
                    "keys":{
                        "Bloc0":{
                            "type": "entradaDadesBlocs",
                            "mandatory": true
                        },
                        "Bloc1":{
                            "type": "entradaDadesBlocs",
                            "mandatory": true
                        },
                        "Bloc2":{
                            "type": "entradaDadesBlocs",
                            "mandatory": true
                        },
                        "Bloc3":{
                            "type": "entradaDadesBlocs",
                            "mandatory": true
                        },
                        "Bloc4":{
                            "type": "entradaDadesBlocs",
                            "mandatory": true
                        },
                        "Bloc5":{
                            "type": "entradaDadesBlocs",
                            "mandatory": true
                        },
                        "Bloc6":{
                            "type": "entradaDadesBlocs",
                            "mandatory": true
                        }
                    },
                    "mandatory":true
                }, 
                
                
                
                
                
                        "dadesBlocs": {
                            "type":"object",
                            "typeDef": "defTaulaDadesBlocs",
                            "mandatory": true,
                            "default":[
                                "Bloc0":{"id":"Bloc 0","inici":"","final":"","nom":"nom del bloc"},
                                "Bloc1":{"id":"Bloc 1","inici":"","final":"","nom":"nom del bloc"},
                                "Bloc2":{"id":"Bloc 2","inici":"","final":"","nom":"nom del bloc"},
                                "Bloc3":{"id":"Bloc 3","inici":"","final":"","nom":"nom del bloc"},
                                "Bloc4":{"id":"Bloc 4","inici":"","final":"","nom":"nom del bloc"},
                                "Bloc5":{"id":"Bloc 5","inici":"","final":"","nom":"nom del bloc"},
                                "Bloc6":{"id":"Bloc 6","inici":"","final":"","nom":"nom del bloc"}
                            ]
                        }
                        
                        
                        
                        
                        
Uncaught Error: Cannot create references to/from string offsets in 
/home/professor/Documents/projectes_wiki/wikiDev/dokuwiki_30/lib/lib_ioc/wikiiocmodel/ProjectUpdateProcessor.php:159\n

Stack trace:\n
#0 /home/professor/Documents/projectes_wiki/wikiDev/dokuwiki_30/lib/lib_ioc/wikiiocmodel/ProjectUpdateProcessor.php(96): FieldProjectUpdateProcessor::runProcessField(Object(FieldSubstitutionProjectUpdateProcessor), 'dadesBlocs#01#i...', Array)\n
#1 /home/professor/Documents/projectes_wiki/wikiDev/dokuwiki_30/lib/lib_ioc/wikiiocmodel/ProjectUpdateProcessor.php(82): AbstractProjectUpdateProcessor->runProcessField('dadesBlocs#01#i...', Array)\n
#2 /home/professor/Documents/projectes_wiki/wikiDev/dokuwiki_30/lib/lib_ioc/wikiiocmodel/ProjectUpdateProcessor.php(27): AbstractProjectUpdateProcessor->runProcess(Array)\n
#3 /home/professor/Documents/projectes_wiki/wikiDev/dokuwiki_30/lib/plugins/projectsdev/projects/guieseoi/actions/ProjectUpdateDataAction.php(42): ManagerProjectUpdateProcessor::updateAll(Array, Array)\n
#4 /home/pro in /home/professor/Documents/projectes_wiki/wikiDev/dokuwiki_30/lib/lib_ioc/wikiiocmodel/ProjectUpdateProcessor.php on line 159, referer: http://localhost/dokuwiki_30/doku.php


         "{\"field\":\"dataBloc11\",\"concat\":[{\"type\":\"literal\",\"value\":\"\\/\"},{\"type\":\"function\",\"name\":\"date\",\"parameters\":\"Y\"}],\"returnType\":\"future_date\"}"
"{\"field\":\"dadesBlocs#01#inici\",\"concat\":[{\"type\":\"literal\",\"value\":\"\\/\"},{\"type\":\"function\",\"name\":\"date\",\"parameters\":\"Y\"}],\"returnType\":\"future_date\"}"