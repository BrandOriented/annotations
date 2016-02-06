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

namespace Doctrine\Annotations;

use Doctrine\Annotations\Exception\ClassNotFoundException;

/**
 * Resolve a annotation class name
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class Resolver
{
    /**
     * @param Context $context
     * @param string  $className
     *
     * @return string
     *
     * @throws \Exception
     */
    public function resolve(Context $context, string $className) : string
    {
        $isFullyQualified   = '\\' === $className[0];
        $contextDescription = $context->getDescription();

        if ($isFullyQualified && $this->classExists($className)) {
            return $className;
        }

        if ($isFullyQualified) {
            throw ClassNotFoundException::annotationNotFound($className, $contextDescription);
        }

        if ( ($fqcn = $this->resolveNamespace($className, $context->getNamespace())) !== null) {
            return $fqcn;
        }

        if ( ($fqcn = $this->resolveImports($className, $context->getImports())) !== null) {
            return $fqcn;
        }

        if ($this->classExists($className)) {
            return $className;
        }

        throw ClassNotFoundException::annotationNotImported($className, $contextDescription);
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function classExists($class) : bool
    {
        return class_exists($class) || interface_exists($class);
    }

    /**
     * @param string $className
     * @param string $namespace
     *
     * @return string|null
     */
    private function resolveNamespace($class, $namespace)
    {
        if ($this->classExists($namespace . '\\' . $class)) {
            return $namespace . '\\' . $class;
        }

        return null;
    }

    /**
     * @param string $className
     * @param array  $imports
     *
     * @return string|null
     */
    private function resolveImports($name, array $imports)
    {
        $index = strpos($name, '\\');
        $alias = strtolower($name);

        if ($index !== false) {
            $part  = substr($name, 0, $index);
            $alias = strtolower($part);
        }

        if ( ! isset($imports[$alias])) {
            return null;
        }

        if ($index === false) {
            return $imports[$alias];
        }

        return $imports[$alias] . substr($name, $index);
    }
}
