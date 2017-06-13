<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

declare(strict_types=1);

namespace Doctrine\Common\Annotations\Parser;

use Reflector;
use ReflectionClass;
use ReflectionProperty;

use Doctrine\Common\Annotations\Context;
use Doctrine\Common\Annotations\Resolver;
use Doctrine\Common\Annotations\Annotation\Type;
use Doctrine\Common\Annotations\Exception\ParserException;

/**
 * A parser for annotations metadata.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class MetadataParser
{
    /**
     * @var \Doctrine\Common\Annotations\Parser\HoaParser
     */
    private $parser;

    /**
     * @var array
     */
    private $imports = [
        'annotation'       => 'Doctrine\Common\Annotations\Annotation',
        'type'             => 'Doctrine\Common\Annotations\Annotation\Type',
        'enum'             => 'Doctrine\Common\Annotations\Annotation\Enum',
        'target'           => 'Doctrine\Common\Annotations\Annotation\Target',
        'ignoreannotation' => 'Doctrine\Common\Annotations\Annotation\IgnoreAnnotation'
    ];

    /**
     * Constructor
     *
     * @param \Doctrine\Common\Annotations\Parser\HoaParser $parser
     * @param \Doctrine\Common\Annotations\Resolver         $resolver
     */
    public function __construct(HoaParser $parser, Resolver $resolver)
    {
        $this->resolver = $resolver;
        $this->parser   = $parser;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return array
     */
    public function parseAnnotationClass(ReflectionClass $class) : array
    {
        $docblock    = $class->getDocComment();
        $namespace   = $class->getNamespaceName();
        $annotations = $this->parseDockblock($class, $namespace, $docblock);

        return $annotations;
    }

   /**
     * @param \ReflectionProperty $property
     *
     * @return array
     */
    public function parseAnnotationProperty(ReflectionProperty $property) : array
    {
        $matches     = null;
        $docblock    = $property->getDocComment();
        $class       = $property->getDeclaringClass();
        $namespace   = $class->getNamespaceName();
        $annotations = $this->parseDockblock($property, $namespace, $docblock);

        if ($docblock && preg_match('/@var\s+([^\s]+)/', $docblock, $matches)) {
            $annotations[] = new Type(['value' => $matches[1]]);
        }

        return $annotations;
    }

    /**
     * @param \Reflector  $reflector
     * @param string      $namespace
     * @param string|bool $docblock
     *
     * @return array
     */
    private function parseDockblock(Reflector $reflector, $namespace, $docblock) : array
    {
        if ($docblock == false) {
            return [];
        }

        try {
            $context   = new Context($reflector, [], $this->imports);
            $visitor   = new MetadataVisitor($this->resolver, $context);
            $result    = $this->parser->parseDockblock($docblock, $visitor);

            return $result;
        } catch (\Hoa\Compiler\Exception $e) {
            throw ParserException::hoaException($e, $context->getDescription());
        }
    }
}
