<?php

namespace PhpPdgAnalysis\Slicing;

use PhpParser\Node;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitorAbstract;
use PhpPdg\Graph\Node\NodeInterface;

class SlicingVisitor extends NodeVisitorAbstract {
	private $match_lines;
	private $subnodes_in_array;

	public function __construct($match_lines) {
		$this->match_lines = $match_lines;
		$this->subnodes_in_array = new \SplObjectStorage();
	}

	public function enterNode(Node $node) {
		foreach ($node->getSubNodeNames() as $name) {
			if (is_array($node->$name) === true) {
				$this->subnodes_in_array->attach($node);    // track nodes that can be removed from arrays
			}
		}
	}

	public function leaveNode(Node $node) {
		if ($this->subnodes_in_array->contains($node) === true) {
			if ($this->nodeMatches($node) === false) {
				return NodeTraverserInterface::REMOVE_NODE;
			}
			$this->subnodes_in_array->detach($node);
		}
		return false;
	}

	private function nodeMatches(Node $node) {
		if (isset($match_lines[$node->getLine()]) === true) {
			return true;
		}
		foreach ($node->getSubNodeNames() as $name) {
			if (is_array($node->$name) === true) {
				if (empty($node->$name) === false) {
					return true;
				}
			} else if (is_object($node->$name) === true && $node->$name instanceof NodeInterface) {
				if ($this->nodeMatches($node->$name) === true) {
					return true;
				}
			} else {
				throw new \LogicException("Should not be possible");
			}
		}
		return false;
	}
}