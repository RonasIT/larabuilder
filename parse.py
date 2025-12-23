array:1 [
  0 => PhpParser\Node\Stmt\Return_ {#1622
    #attributes: array:6 [
      "startLine" => 3
      "startTokenPos" => 2
      "startFilePos" => 7
      "endLine" => 14
      "endTokenPos" => 76
      "endFilePos" => 371
    ]
    +expr: PhpParser\Node\Expr\MethodCall {#1621
      #attributes: array:6 [
        "startLine" => 3
        "startTokenPos" => 4
        "startFilePos" => 14
        "endLine" => 14
        "endTokenPos" => 75
        "endFilePos" => 370
      ]
      +var: PhpParser\Node\Expr\MethodCall {#1619
        +name: PhpParser\Node\Identifier {#1589
          +name: "withExceptions"
        }
        +args: array:1 [
          0 => PhpParser\Node\Arg {#1618
            +value: PhpParser\Node\Expr\Closure {#1617
              +stmts: array:2 [
                0 => PhpParser\Node\Stmt\Expression {#1604
                  +expr: PhpParser\Node\Expr\MethodCall {#1603
                    +name: PhpParser\Node\Identifier {#1594
                      +name: "render"
                    }
                    +args: array:1 [
                      0 => PhpParser\Node\Arg {#1602
                        +value: PhpParser\Node\Expr\Closure {#1601
                          +params: array:1 [
                            0 => PhpParser\Node\Param {#1597
                              +type: PhpParser\Node\Name {#1595
                                +name: "ExpectationFailedException"
                              }
                            }
                          ]
                        }
                      }
                    ]
                  }
                }
                1 => PhpParser\Node\Stmt\Expression {#1616
                  +expr: PhpParser\Node\Expr\MethodCall {#1615
                    +name: PhpParser\Node\Identifier {#1606
                      +name: "dontFlash"
                    }
                    +args: array:1 [
                      0 => PhpParser\Node\Arg {#1614
                        +name: null
                        +value: PhpParser\Node\Expr\Array_ {#1613
                          +items: array:2 [
                            0 => PhpParser\Node\ArrayItem {#1608
                              +key: null
                              +value: PhpParser\Node\Scalar\String_ {#1607
                                +value: "password"
                              }
                              +byRef: false
                              +unpack: false
                            }
                            1 => PhpParser\Node\ArrayItem {#1610
                              +key: null
                              +value: PhpParser\Node\Scalar\String_ {#1609
                                +value: "password_confirmation"
                              }

                            }
                          ]
                        }
                      }
                    ]
                  }
                }
              ]
            }
          }
        ]
      }
      +name: PhpParser\Node\Identifier {#1620
        #attributes: array:6 [
          "startLine" => 14
          "startTokenPos" => 73
          "startFilePos" => 363
          "endLine" => 14
          "endTokenPos" => 73
          "endFilePos" => 368
        ]
        +name: "create"
      }
      +args: []
    }
  }
] // /var/www/open-source/larabuilder/src/PHPFileBuilder.php:30
Fatal error: Premature end of PHP process when running RonasIT\Larabuilder\Tests\PHPFileBuilderTest::testSetMethodCallBodyCustom.
