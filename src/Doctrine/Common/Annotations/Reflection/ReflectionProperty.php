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

namespace Doctrine\Common\Annotations\Reflection;

use Doctrine\Common\Annotations\Parser\PhpParser;

/**
 * Reflection Property
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ReflectionProperty extends \ReflectionProperty
{
    /**
     * @var \Doctrine\Common\Annotations\Parser\PhpParser
     */
    private $phpParser;

    /**
     * @var array
     */
    private $imports;

    /**
     * @var \Doctrine\Common\Annotations\Reflection\ReflectionClass
     */
    private $declaringClass;

    /**
     * Constructor.
     *
     * @param string                                 $className
     * @param string                                 $propertyName
     * @param \Doctrine\Common\Annotations\Parser\PhpParser $phpParser
     */
    public function __construct(string $className, string $propertyName, PhpParser $phpParser)
    {
        parent::__construct($className, $propertyName);

        $this->phpParser = $phpParser;
    }

    /**
     * @return array
     */
    public function getImports() : array
    {
        if ($this->imports !== null) {
            return $this->imports;
        }

        $class        = $this->getDeclaringClass();
        $classImports = $class->getImports();
        $traitImports = [];

        foreach ($class->getTraits() as $trait) {
            if ( ! $trait->hasProperty($this->getName())) {
                continue;
            }

            $propertyImports = $this->phpParser->parseClass($trait);
            $traitImports    = array_merge($traitImports, $propertyImports);
        }

        return $this->imports = array_merge($classImports, $traitImports);
    }

    /**
     * @return \Doctrine\Common\Annotations\Reflection\ReflectionClass
     */
    public function getDeclaringClass() : ReflectionClass
    {
        if ($this->declaringClass !== null) {
            return $this->declaringClass;
        }

        $className  = parent::getDeclaringClass()->name;
        $reflection = new ReflectionClass($className, $this->phpParser);

        return $this->declaringClass = $reflection;
    }
}
